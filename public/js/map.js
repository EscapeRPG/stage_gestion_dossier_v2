document.addEventListener('DOMContentLoaded', async () => {
    const map = L.map('map').setView([47.218371, -1.553621], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const urlParams = new URLSearchParams(window.location.search);
    const numInt = urlParams.get('numInt');
    const dateParam = urlParams.get('date');

    let techniciensDisponibles = [];
    let codeAgence = '';

    try {
        const res = await fetch(`/api/intervention/${numInt}`);
        const data = await res.json();
        if (data.success && data.data) {
            const inter = data.data;
            codeAgence = inter.codeAgence;

            techniciensDisponibles = Object.entries(inter.salaries).flatMap(([groupe, noms]) =>
                noms.map(n => ({ groupe, nom: n }))
            );
        }
    } catch (e) {
        console.error('Erreur lors du chargement des techniciens :', e);
    }

    const mapDiv = document.getElementById('map');
    const client = JSON.parse(mapDiv.dataset.client || 'null');
    const entreprise = JSON.parse(mapDiv.dataset.entreprise || 'null');
    const rdvListDiv = document.getElementById('rdvList');

    async function geocode(address) {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`);
        const results = await response.json();
        return results.length > 0 ? [parseFloat(results[0].lat), parseFloat(results[0].lon)] : null;
    }

    let rdvs = [];

    try {
        const res = await fetch(`/api/rdv?date=${dateParam}`);
        rdvs = await res.json();
    } catch (e) {
        rdvListDiv.textContent = 'Erreur lors du chargement des RDV';
    }

    const rdvsByTech = {};
    rdvs.forEach(rdv => {
        if (!rdvsByTech[rdv.technicien]) rdvsByTech[rdv.technicien] = [];
        rdvsByTech[rdv.technicien].push(rdv);
    });

    const techs = Object.keys(rdvsByTech);
    const colors = ['orange', 'purple', 'cyan', 'blue', 'magenta', 'yellow'];
    const markersMap = {};

    const entrepriseCoords = [entreprise.lat, entreprise.lon];
    const clientCoords = client ? await geocode(`${client.Adresse_Cli}, ${client.CP_Cli} ${client.Ville_Cli}`) : null;

    if (entrepriseCoords) {
        L.marker(entrepriseCoords, {
            icon: L.icon({ iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png', iconSize: [32, 32] })
        }).addTo(map).bindPopup(`<b>${entreprise.nom}</b><br>${entreprise.adresse}`);
    }

    if (clientCoords) {
        L.marker(clientCoords, {
            icon: L.icon({ iconUrl: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png', iconSize: [32, 32] })
        }).addTo(map).bindPopup(`<b>${client.Nom_Cli}</b><br>${client.Adresse_Cli}<br>${client.CP_Cli} ${client.Ville_Cli}`);
    }

    async function renderAllTechRoutes() {
        rdvListDiv.innerHTML = '';
        for (let i = 0; i < techs.length; i++) {
            const tech = techs[i];
            const baseColor = colors[i % colors.length];

            await renderTechnicienRoute(tech, baseColor);
        }
    }

    async function renderTechnicienRoute(tech, baseColor) {
        if (markersMap[tech]) {
            markersMap[tech].forEach(m => map.removeLayer(m));
        }
        markersMap[tech] = [];

        const rdvsTech = rdvsByTech[tech] || [];
        if (!rdvsTech.length) return;

        const rgbaColors = {
            orange: 'rgba(255, 165, 0, 0.1)',
            purple: 'rgba(128, 0, 128, 0.1)',
            cyan: 'rgba(0, 255, 255, 0.1)',
            blue: 'rgba(0, 0, 255, 0.1)',
            magenta: 'rgba(255, 0, 255, 0.1)',
            yellow: 'rgba(255, 255, 0, 0.1)'
        };
        const bgColor = rgbaColors[baseColor] || 'rgba(128,128,128,0.5)';

        const div = document.createElement('div');
        div.classList.add('tech');
        div.dataset.tech = tech;
        div.style.maxHeight = '22px';
        div.style.backgroundColor = bgColor;

        const sticker = document.createElement('div');
        sticker.classList.add('color-tech');
        sticker.style.backgroundColor = baseColor;
        div.appendChild(sticker);

        const btn = document.createElement('button');
        btn.textContent = '+';
        btn.addEventListener('click', () => {
            if (btn.textContent === '-') {
                div.style.maxHeight = '22px';
                btn.textContent = '+';
            } else {
                div.style.maxHeight = div.scrollHeight + 'px';
                btn.textContent = '-';
            }
        });
        div.appendChild(btn);

        const h3 = document.createElement('h3');
        h3.innerHTML = tech;
        h3.style.color = 'black';
        div.appendChild(h3);

        const rdvCoordsList = await Promise.all(rdvsTech.map(async (rdv, idx) => {
            const rdvDiv = document.createElement('div');
            rdvDiv.classList.add('rdv-item');
            rdvDiv.innerHTML = `<b>${rdv.nom}</b><br>${rdv.adresse}<br><br><strong>${rdv.heure.slice(0,5)}</strong>`;
            div.appendChild(rdvDiv);

            const coords = await geocode(rdv.adresse);
            return { coords, rdv, idx };
        }));

        rdvListDiv.appendChild(div);

        const validCoords = rdvCoordsList.filter(x => x.coords !== null).map(x => x.coords);
        if (!validCoords.length) return;

        let routePoints = [entrepriseCoords];
        let remaining = [...validCoords];
        let farthestIdx = remaining.reduce((maxIdx, cur, i) =>
            distance(entrepriseCoords, cur) > distance(entrepriseCoords, remaining[maxIdx]) ? i : maxIdx, 0);
        routePoints.push(remaining[farthestIdx]);
        remaining.splice(farthestIdx, 1);
        while (remaining.length) {
            const last = routePoints[routePoints.length - 1];
            let nearestIdx = remaining.reduce((minIdx, cur, i) => distance(last, cur) < distance(last, remaining[minIdx]) ? i : minIdx, 0);
            routePoints.push(remaining[nearestIdx]);
            remaining.splice(nearestIdx, 1);
        }
        routePoints.push(entrepriseCoords);

        const url = `https://router.project-osrm.org/route/v1/driving/` +
            routePoints.map(c => c[1] + ',' + c[0]).join(';') +
            `?overview=full&geometries=geojson`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            if (data.code === 'Ok') {
                const route = data.routes[0];
                const line = L.geoJSON(route.geometry, { color: baseColor, weight: 4 }).addTo(map);
                markersMap[tech].push(line);

                for (let idx = 1; idx < routePoints.length - 1; idx++) {
                    const coord = routePoints[idx];
                    const rdv = rdvCoordsList[idx - 1].rdv;
                    const marker = L.marker(coord, { icon: L.divIcon({
                            html: `<div style="background:${baseColor};border-radius:50%;width:24px;height:24px;color:white;text-align:center;line-height:24px;font-weight:bold;">${idx}</div>`,
                            className: ''
                        })}).addTo(map);
                    marker.rdv = rdv;

                    marker.on('click', () => {
                        const groupes = {};
                        techniciensDisponibles.forEach(t => {
                            if (!groupes[t.groupe]) groupes[t.groupe] = [];
                            groupes[t.groupe].push(t.nom);
                        });

                        const optionsHtml = Object.entries(groupes).map(([groupe, noms]) => `
                            <optgroup label="${groupe}">
                                ${noms.map(n => `<option value="${n}">${n}</option>`).join('')}
                            </optgroup>
                        `).join('');

                        const popupDiv = document.createElement('div');
                        popupDiv.innerHTML = `
                            <b>${rdv.nom}</b><br>${rdv.adresse}<br>
                            <label for="selectTech">Réaffecter à :</label><br>
                            <select id="selectTech" style="width: 100%; margin: 5px 0;">
                                <option value="">-- Choisir un technicien --</option>
                                ${optionsHtml}
                            </select>
                            <button id="btnReaffect" style="margin-top:5px; width:100%;">Valider</button>
                        `;

                        const popup = L.popup()
                            .setLatLng(marker.getLatLng())
                            .setContent(popupDiv)
                            .openOn(map);

                        popupDiv.querySelector('#btnReaffect').addEventListener('click', async () => {
                            const newTech = popupDiv.querySelector('#selectTech').value;
                            if (!newTech) {
                                alert('Veuillez choisir un technicien');
                                return;
                            }
                            await reassignTechnicien(rdv, newTech);
                            map.closePopup();
                        });
                    });

                    markersMap[tech].push(marker);
                }

                const distanceKm = (route.distance / 1000).toFixed(1);
                const durationMin = Math.round(route.duration / 60);
                const info = document.createElement('div');
                info.classList.add('route-info');
                info.innerHTML = `<b>Distance:</b> ${distanceKm} km<br><b>Durée:</b> ${durationMin} min`;
                div.appendChild(info);
            }
        } catch (e) {
            console.error('Erreur OSRM', e);
        }
    }

    async function reassignTechnicien(rdv, newTech) {
        try {
            const res = await fetch(`/api/rdv/${rdv.num}/reassign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ technicien: newTech })
            });
            const data = await res.json();
            if (data.success) {
                const oldTech = rdv.technicien;
                rdv.technicien = newTech;

                rdvsByTech[oldTech] = rdvsByTech[oldTech].filter(r => r.num !== rdv.num);
                if (!rdvsByTech[newTech]) rdvsByTech[newTech] = [];
                rdvsByTech[newTech].push(rdv);

                await renderAllTechRoutes();
                alert('Technicien réaffecté avec succès !');
            } else {
                alert('Erreur lors de la réaffectation');
            }
        } catch(e) {
            console.error(e);
            alert('Erreur réseau lors de la réaffectation');
        }
    }

    function distance([lat1, lon1], [lat2, lon2]) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLon/2)**2;
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    await renderAllTechRoutes();
});

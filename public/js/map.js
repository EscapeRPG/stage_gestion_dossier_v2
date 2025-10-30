document.addEventListener('DOMContentLoaded', async () => {
    const map = L.map('map').setView([47.218371, -1.553621], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const mapDiv = document.getElementById('map');
    const client = JSON.parse(mapDiv.dataset.client || 'null');
    const entreprise = JSON.parse(mapDiv.dataset.entreprise || 'null');
    const rdvListDiv = document.getElementById('rdvList');

    async function geocode(address) {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`);
        const results = await response.json();
        return results.length > 0 ? [parseFloat(results[0].lat), parseFloat(results[0].lon)] : null;
    }

    const dateParam = new URLSearchParams(window.location.search).get('date');
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
    const markers = [];
    const colors = ['orange', 'purple', 'cyan', 'blue', 'magenta', 'yellow'];
    const techs = Object.keys(rdvsByTech);
    const entrepriseCoords = [entreprise.lat, entreprise.lon];
    const clientCoords = client ? await geocode(`${client.Adresse_Cli}, ${client.CP_Cli} ${client.Ville_Cli}`) : null;

    if (entrepriseCoords) {
        L.marker(entrepriseCoords, {
            icon: L.icon({
                iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                iconSize: [32, 32],
            })
        }).addTo(map).bindPopup(`<b>${entreprise.nom}</b><br>${entreprise.adresse}`);
        markers.push(entrepriseCoords);
    }

    if (clientCoords) {
        L.marker(clientCoords, {
            icon: L.icon({
                iconUrl: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
                iconSize: [32, 32],
            })
        }).addTo(map).bindPopup(`<b>${client.Nom_Cli}</b><br>${client.Adresse_Cli}<br>${client.CP_Cli} ${client.Ville_Cli}`);
        markers.push(clientCoords);
    }

    rdvListDiv.innerHTML = '';
    const allMarkerPromises = techs.map(async (tech, i) => {
        const baseColor = colors[i % colors.length];
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
        div.style.maxHeight = '22px';
        div.style.backgroundColor = bgColor;

        const sticker = document.createElement('div');
        sticker.classList.add('color-tech');
        sticker.style.backgroundColor = baseColor;
        div.appendChild(sticker);

        const button = document.createElement('button');
        button.textContent = '+';
        button.addEventListener('click', () => {
            if (button.textContent === '-') {
                div.style.maxHeight = '22px';
                button.textContent = '+';
            } else {
                div.style.maxHeight = div.scrollHeight + 'px';
                button.textContent = '-';
            }
        });
        div.appendChild(button);

        const h3 = document.createElement('h3');
        h3.innerHTML = tech;
        h3.style.color = 'black';
        div.appendChild(h3);

        const rdvCoordsList = await Promise.all(rdvsByTech[tech].map(async (rdv, idx) => {
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

        let farthestIdx = remaining.reduce((maxIdx, cur, i) => distance(entrepriseCoords, cur) > distance(entrepriseCoords, remaining[maxIdx]) ? i : maxIdx, 0);
        routePoints.push(remaining[farthestIdx]);
        remaining.splice(farthestIdx,1);

        while (remaining.length) {
            const last = routePoints[routePoints.length-1];
            let nearestIdx = remaining.reduce((minIdx, cur, i) => distance(last, cur) < distance(last, remaining[minIdx]) ? i : minIdx, 0);
            routePoints.push(remaining[nearestIdx]);
            remaining.splice(nearestIdx,1);
        }

        routePoints.push(entrepriseCoords);

        const url = `https://router.project-osrm.org/route/v1/driving/` +
            routePoints.map(c => c[1]+','+c[0]).join(';') +
            `?overview=full&geometries=geojson`;
        try {
            const res = await fetch(url);
            const data = await res.json();
            if (data.code === 'Ok') {
                const route = data.routes[0];
                L.geoJSON(route.geometry, { color: baseColor, weight: 4 }).addTo(map);

                routePoints.slice(1,-1).forEach((coord, idx) => {
                    const rdv = rdvCoordsList[idx].rdv;
                    L.marker(coord, {
                        icon: L.divIcon({
                            html: `<div style="background:${baseColor};border-radius:50%;width:24px;height:24px;color:white;text-align:center;line-height:24px;font-weight:bold;">${idx+1}</div>`,
                            className: ''
                        })
                    }).addTo(map).bindPopup(`<b>${rdv.nom}</b><br>${rdv.adresse}<br><b>Technicien:</b> ${tech}<br><b>Ordre:</b> ${idx+1}`);
                });

                const distanceKm = (route.distance / 1000).toFixed(1);
                const durationMin = Math.round(route.duration / 60);
                const info = document.createElement('div');
                info.classList.add('route-info');
                info.innerHTML = `<b>Distance:</b> ${distanceKm} km<br><b>Durée:</b> ${durationMin} min`;
                div.appendChild(info);
            }
        } catch(e) {
            console.error('Erreur OSRM', e);
        }
    });

    await Promise.all(allMarkerPromises);
    if (markers.length) map.fitBounds(markers, {padding:[50,50]});

    function distance([lat1, lon1], [lat2, lon2]){
        const R=6371;
        const dLat=(lat2-lat1)*Math.PI/180;
        const dLon=(lon2-lon1)*Math.PI/180;
        const a=Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLon/2)**2;
        const c=2*Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R*c;
    }
});

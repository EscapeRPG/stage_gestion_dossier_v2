import { geocode, distance } from '../utils/geocode.js';
import { reassignRdv } from '../api/api.js';
import { COLORS, COLOR_BG } from '../config/mapColors.js';

export async function renderAllTechRoutes(map, rdvs, techniciensDisponibles, entrepriseCoords, clientCoords) {
    const rdvListDiv = document.getElementById('rdvList');
    rdvListDiv.innerHTML = '';

    const rdvsByTech = {};
    rdvs.forEach(rdv => {
        if (!rdvsByTech[rdv.technicien]) rdvsByTech[rdv.technicien] = [];
        rdvsByTech[rdv.technicien].push(rdv);
    });

    const techs = Object.keys(rdvsByTech);
    const markersMap = {};

    if (entrepriseCoords) {
        L.marker(entrepriseCoords, {
            icon: L.icon({ iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png', iconSize: [32, 32] })
        }).addTo(map).bindPopup(`<b>Maintronic</b><br>${entrepriseCoords.adresse}`);
    }

    if (clientCoords) {
        L.marker(clientCoords, {
            icon: L.icon({ iconUrl: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png', iconSize: [32, 32] })
        }).addTo(map).bindPopup(`<b>Client : ${clientCoords.nom}</b><br>${clientCoords.adresse}<br>${clientCoords.CPVille}<br>${clientCoords.machineClient}`);
    }

    for (let i = 0; i < techs.length; i++) {
        const tech = techs[i];
        const baseColor = COLORS[i % COLORS.length];
        await renderTechnicienRoute(map, rdvListDiv, rdvsByTech, tech, baseColor, techniciensDisponibles, entrepriseCoords, markersMap);
    }
}

async function renderTechnicienRoute(map, rdvListDiv, rdvsByTech, tech, baseColor, techniciensDisponibles, entrepriseCoords, markersMap) {
    if (markersMap[tech]) {
        markersMap[tech].forEach(m => map.removeLayer(m));
    }
    markersMap[tech] = [];

    const rdvsTech = rdvsByTech[tech] || [];
    if (!rdvsTech.length) return;

    const div = document.createElement('div');
    div.classList.add('tech');
    div.dataset.tech = tech;
    div.style.maxHeight = '22px';
    div.style.backgroundColor = COLOR_BG[baseColor] || 'rgba(128,128,128,0.5)';

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
    h3.textContent = tech;
    h3.style.color = 'black';
    div.appendChild(h3);

    const rdvCoordsList = await Promise.all(rdvsTech.map(async (rdv, idx) => {
        const rdvDiv = document.createElement('div');
        rdvDiv.classList.add('rdv-item');
        rdvDiv.innerHTML = `
            <strong>${rdv.heure.slice(0, 5)}</strong>
            <br>
            <br>
            <b>${rdv.nom}</b>
            <br>
            ${rdv.adresse}
            <br>
            ${rdv.machineClient}
        `;
        div.appendChild(rdvDiv);

        const coords = await geocode(rdv.adresse);
        return { coords, rdv, idx };
    }));

    rdvListDiv.appendChild(div);

    const validCoords = rdvCoordsList.filter(x => x.coords !== null).map(x => x.coords);
    if (!validCoords.length) return;

    const routePoints = calcRouteOrder([entrepriseCoords.lat, entrepriseCoords.lon], validCoords);

    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/` +
        routePoints.map(c => c[1] + ',' + c[0]).join(';') +
        `?overview=full&geometries=geojson`;

    try {
        const res = await fetch(osrmUrl);
        const data = await res.json();
        if (data.code === 'Ok') {
            const route = data.routes[0];
            const line = L.geoJSON(route.geometry, { color: baseColor, weight: 4 }).addTo(map);
            markersMap[tech].push(line);

            for (let idx = 1; idx < routePoints.length - 1; idx++) {
                const coord = routePoints[idx];
                const rdv = rdvCoordsList[idx - 1].rdv;
                const marker = createRdvMarker(coord, baseColor, idx, map, rdv, techniciensDisponibles, rdvsByTech);
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

function createRdvMarker(coord, baseColor, idx, map, rdv, techniciensDisponibles, rdvsByTech) {
    const marker = L.marker(coord, {
        icon: L.divIcon({
            html: `<div style="background:${baseColor};border-radius:50%;width:24px;height:24px;color:white;text-align:center;line-height:24px;font-weight:bold;">${idx}</div>`,
            className: ''
        })
    }).addTo(map);

    marker.on('click', () => openReassignPopup(marker, rdv, map, techniciensDisponibles, rdvsByTech));
    return marker;
}

function openReassignPopup(marker, rdv, map, techniciensDisponibles, rdvsByTech) {
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
        <b>${rdv.nom}</b>
        <br>
        ${rdv.adresse}
        <br>
        ${rdv.machineClient}
        <br>
        <br>
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
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const data = await reassignRdv(rdv, newTech, csrf);
        if (data.success) {
            alert('Technicien réaffecté avec succès !');
            map.closePopup();
            window.location.reload();
        } else {
            alert('Erreur lors de la réaffectation');
        }
    });
}

function calcRouteOrder(entrepriseCoords, validCoords) {
    let routePoints = [entrepriseCoords];
    let remaining = [...validCoords];
    let farthestIdx = remaining.reduce((maxIdx, cur, i) =>
        distance(entrepriseCoords, cur) > distance(entrepriseCoords, remaining[maxIdx]) ? i : maxIdx, 0);
    routePoints.push(remaining[farthestIdx]);
    remaining.splice(farthestIdx, 1);
    while (remaining.length) {
        const last = routePoints[routePoints.length - 1];
        let nearestIdx = remaining.reduce((minIdx, cur, i) =>
            distance(last, cur) < distance(last, remaining[minIdx]) ? i : minIdx, 0);
        routePoints.push(remaining[nearestIdx]);
        remaining.splice(nearestIdx, 1);
    }
    routePoints.push(entrepriseCoords);
    return routePoints;
}

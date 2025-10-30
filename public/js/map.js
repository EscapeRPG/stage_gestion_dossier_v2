document.addEventListener('DOMContentLoaded', async () => {
    // Récupère la date passée dans l’URL
    const urlParams = new URLSearchParams(window.location.search);
    const date = urlParams.get('date');

    // Initialise la carte
    const map = L.map('map').setView([47.2184, -1.5536], 10); // centre sur Nantes
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Récupère les RDV du jour
    const response = await fetch(`/api/rdv?date=${date}`);
    const rdvs = await response.json();

    const listContainer = document.getElementById('rdvList');
    listContainer.innerHTML = '';

    // const geocoder = L.Control.Geocoder.nominatim();
    // nécessite leaflet-control-geocoder

    // Pour chaque RDV, afficher dans la liste et sur la carte
    for (const rdv of rdvs) {
        const item = document.createElement('div');
        item.className = 'rdv';
        item.innerHTML = `
            <strong>${rdv.nom}</strong>
            <div>${rdv.heure || '(Heure non définie)'}</div>
            <div>${rdv.technicien || '(Technicien non assigné)'}</div>
            <small>${rdv.adresse}</small>
        `;
        listContainer.appendChild(item);

        // Géocode et ajoute un marqueur
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(rdv.adresse)}`);
        const results = await response.json();
        if (results.length > 0) {
            const { lat, lon } = results[0];
            const marker = L.marker([lat, lon]).addTo(map);
            marker.bindPopup(`<b>${rdv.nom}</b><br>${rdv.adresse}<br>${rdv.heure || ''}`);

            item.addEventListener('click', () => {
                map.setView([lat, lon], 13);
                marker.openPopup();
            });
        }
    }
});

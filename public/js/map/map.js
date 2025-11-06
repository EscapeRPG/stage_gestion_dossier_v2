import { initMap } from './config/initMap.js';
import { getIntervention, getRdvs } from './api/api.js';
import { renderAllTechRoutes } from './ui/rdvList.js';

document.addEventListener('DOMContentLoaded', async () => {
    const map = initMap();

    const urlParams = new URLSearchParams(window.location.search);
    const numInt = urlParams.get('numInt');
    const dateParam = urlParams.get('date');

    const mapDiv = document.getElementById('map');
    const client = JSON.parse(mapDiv.dataset.client || 'null');
    const entreprise = JSON.parse(mapDiv.dataset.entreprise || 'null');

    let techniciensDisponibles = [];
    let rdvs = [];

    try {
        const inter = await getIntervention(numInt);
        if (inter.success && inter.data) {
            techniciensDisponibles = Object.entries(inter.data.salaries).flatMap(([groupe, noms]) =>
                noms.map(n => ({ groupe, nom: n }))
            );
        }
    } catch (e) {
        console.error('Erreur chargement intervention', e);
    }

    try {
        rdvs = await getRdvs(dateParam);
    } catch (e) {
        console.error('Erreur chargement RDV', e);
    }

    const entrepriseCoords = entreprise ? {
        lat: parseFloat(entreprise.lat),
        lon: parseFloat(entreprise.lon),
        adresse: entreprise.adresse
    } : null;

    const lat = parseFloat(client.Lat_Cli);
    const lon = parseFloat(client.Lon_Cli);
    const clientCoords = (!isNaN(lat) && !isNaN(lon)) ? {
        lat,
        lon,
        nom: client.Nom_Cli,
        adresse: client.Adresse_Cli,
        CPVille: client.CP_Cli + ' ' + client.Ville_Cli,
        machineClient: client.Marque + ' - ' + client.Type_App
    } : null;

    await renderAllTechRoutes(map, rdvs, techniciensDisponibles, entrepriseCoords, clientCoords);
});

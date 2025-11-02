import { initMap } from './config/initMap.js';
import { geocode } from './utils/geocode.js';
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

    const entrepriseCoords = entreprise ? {'lat': entreprise.lat, 'lon': entreprise.lon, 'adresse': entreprise.adresse} : null;
    const clientCoords = client
        ? await geocode(`${client.Adresse_Cli}, ${client.CP_Cli} ${client.Ville_Cli}`)
        : null;
    clientCoords['nom'] = client.Nom_Cli;
    clientCoords['adresse'] = client.Adresse_Cli;
    clientCoords['CPVille'] = client.CP_Cli + ' ' + client.Ville_Cli;
    await renderAllTechRoutes(map, rdvs, techniciensDisponibles, entrepriseCoords, clientCoords);
});

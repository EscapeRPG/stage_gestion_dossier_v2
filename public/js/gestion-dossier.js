import {initCalendar} from './calendrier.js';
import {initPlanning} from './planning.js';

document.addEventListener('DOMContentLoaded', () => {
    const infosDiv = document.getElementById('day-info');
    infosDiv.style.maxHeight = infosDiv.scrollHeight + 'px';

    const planning = initPlanning();
    initCalendar(planning.fetchDayData, planning.removeRDVDetail);

    const date = new Date(),
        day = date.getDate().toString().padStart(2, '0'),
        month = (date.getMonth() + 1).toString().padStart(2, '0'),
        year = date.getFullYear(),
        today = `${year}-${month}-${day}`;
    planning.fetchDayData(today);
});

const saveBtn = document.getElementById('save-btn'),
    returnBtn = document.getElementById('return'),
    existingRdv = document.getElementById('existing-rdv'),
    rdvDate = document.getElementById('dateRDV'),
    rdvHeure = document.getElementById('timeRDV'),
    form = document.querySelector('form');

if (saveBtn && existingRdv && rdvDate && rdvHeure) {
    saveBtn.addEventListener('click', (event) => {
        if (rdvDate.value && rdvHeure.value) {
            const date = existingRdv.dataset.date,
                time = existingRdv.dataset.heure,
                tech = existingRdv.dataset.tech,
                valid = existingRdv.dataset.valid;

            event.preventDefault();

            createModal(
                `
                Un rendez-vous <strong>${valid}</strong> existe déjà pour ce dossier :
                <br>
                <strong>${date}</strong> à <strong>${time}</strong> pour <strong>${tech}</strong>.
                <br><br>
                Voulez-vous vraiment le remplacer ?
            `,
                () => form.submit()
            );
        }
    })
}

returnBtn.addEventListener('click', () => {
    location.href = returnBtn.dataset.location;
})

function createModal(message, onConfirm) {
    const overlay = document.createElement('div');
    overlay.className = 'overlay';

    const modal = document.createElement('div');
    modal.className = 'modal';

    modal.innerHTML = `
        <p>${message}</p>
        <div>
            <button id="confirm-yes">Oui</button>
            <button id="confirm-no">Non</button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    document.getElementById('confirm-yes').addEventListener('click', () => {
        overlay.remove();
        onConfirm();
    });

    document.getElementById('confirm-no').addEventListener('click', () => {
        overlay.remove();
    });
}

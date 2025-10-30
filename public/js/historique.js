const historique = document.getElementById('histo'),
    numInt = document.getElementById('numInt').value,
    modalContent = document.querySelector('.modal-content'),
    formattedDate = new Intl.DateTimeFormat('fr-FR', {
        day: 'numeric', month: 'long', year: 'numeric'
    });

document.addEventListener('DOMContentLoaded', () => {
    fetchHistoData(numInt);
})

let resizeTimeout;
window.addEventListener('resize', () => {
    if (!historique.style.maxHeight || historique.style.maxHeight === '0px') return;

    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        document.querySelectorAll('.histo-det').forEach(panel => {
            if (panel.style.maxHeight && panel.style.maxHeight !== '0px') {
                panel.style.maxHeight = panel.scrollHeight + 'px';
            }
        });

        const histo = document.getElementById('histo');
        histo.style.maxHeight = histo.scrollHeight + 'px';
    }, 200);
});


async function fetchHistoData(numInt) {
    try {
        const response = await fetch(`/ajax/histo/${numInt}`);
        const data = await response.json();
        displayHistoData(data);
    } catch (err) {
        console.error('Erreur de chargement', err);
        historique.innerHTML = '<p>Erreur de chargement.</p>';
    }
}

function displayHistoData(data) {
    historique.innerHTML = '';

    if (!data.length) {
        historique.innerHTML = '<p>Aucun historique d’intervention pour ce dossier.</p>';
    } else {
        const ul = document.createElement('ul');
        historique.appendChild(ul);

        let idModif = 1;

        data.forEach(item => {
            const li = document.createElement('li');
            const div = document.createElement('div');
            const div2 = document.createElement('div');
            div.className = 'histo-li';
            let liCodeSal = `<strong>${item.Code_Sal}</strong>`,
                liDate = `${formattedDate.format(new Date(item.Date_MAJ))}`,
                liHeure = `${item.Heure_MAJ.slice(0, 5)}${item.Comm ? ' - ' + item.Comm : ''}`;
            div.innerHTML = `<div>#${idModif} - ${liCodeSal} - ${liDate} : ${liHeure}</div>`;

            const info = document.createElement('span');
            info.className = 'info-rdv';
            info.textContent = 'i';
            info.addEventListener('click', () => showHistoDetail(div2));
            div.appendChild(info);
            li.appendChild(div);

            div2.className = 'histo-det';
            div2.style.maxHeight = '0px';
            generateHistoDetail(item, div2);
            li.appendChild(div2);
            ul.appendChild(li);

            idModif++;
        })
    }
}

function showHistoDetail(li) {
    const histo = document.getElementById('histo');

    if (li.style.maxHeight === '0px') {
        li.style.maxHeight = li.scrollHeight + 'px';
        setTimeout(() => histo.style.maxHeight = histo.scrollHeight + 'px', 501);
    } else {
        li.style.maxHeight = '0px';
        histo.style.maxHeight = histo.scrollHeight + 'px';
    }
}

function generateHistoDetail(item, li) {
    let div, innerDiv, actionDiv,
        etat = false, aFaire = false

    item.reponses.forEach((reponse) => {
        if (reponse.Type === 'État') {
            etat = true;
        }
        if (reponse.Type === 'À Faire') {
            aFaire = true;
        }
    });

    if (etat) {
        div = document.createElement('div');
        div.innerHTML = `<strong>Actions effectuées :</strong>`;
        innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        item.reponses.forEach((reponse) => {
            if (reponse.Type === 'État') {
                actionDiv = document.createElement('div');
                actionDiv.classList.add('actions');
                actionDiv.classList.add('done');
                actionDiv.innerHTML = reponse.Question;
                innerDiv.appendChild(actionDiv);
            }
        });
        div.appendChild(innerDiv);
        li.appendChild(div);
    }

    if (aFaire) {
        let rdv;

        if (item.AFaire_Date && item.AFaire_Heure) {
            rdv = `À faire le ${formattedDate.format(new Date(item.AFaire_Date))} à ${item.AFaire_Heure.slice(0, 5)} par ${item.Tech_Affecte}`;
        } else {
            rdv = `À faire par ${item.Tech_Affecte}`;
        }

        div = document.createElement('div');
        div.innerHTML = `<strong>${rdv} :</strong>`;
        innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        item.reponses.forEach((reponse) => {
            if (reponse.Type === `À Faire`) {
                actionDiv = document.createElement('div');
                actionDiv.classList.add('actions');
                actionDiv.classList.add('todo');
                actionDiv.innerHTML = reponse.Question;
                innerDiv.appendChild(actionDiv);
            }
        })
        div.appendChild(innerDiv);
        li.appendChild(div)
    }

    if (item.Comm_Detail) {
        div = document.createElement('div');
        div.innerHTML = `<strong>Commentaire :</strong>`;
        innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        innerDiv.innerHTML = item.Comm_Detail;
        div.appendChild(innerDiv);
        li.appendChild(div);
    }

    if (item.Date_RDV && item.Heure_RDV) {
        console.log(item);
        let valid = '';
        if (item.Valid_RDV === 'O') {
            valid = '<div class="rdv valid">Validé</div>';
        } else {
            valid = '<div class="rdv not-valid">Non validé</div>';
        }

        div = document.createElement('div');
        div.innerHTML = `<strong>Rendez-vous :</strong>`;
        innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        innerDiv.innerHTML = `${valid} <div class="actions">Le ${formattedDate.format(new Date(item.Date_RDV))} à ${item.Heure_RDV.slice(0, 5)} : <strong>${item.Tech_RDV}</strong></div>`;
        div.appendChild(innerDiv);
        li.appendChild(div);
    }
}

function showHistoDetailOLD(item) {
    modal.classList.remove('hidden');

    let div, innerDiv, actionDiv, etat = false, aFaire = false;

    div = document.createElement('div');
    div.className = 'author';
    div.innerHTML = `Le ${formattedDate.format(new Date(item.Date_MAJ))} à ${item.Heure_MAJ.slice(0, 5)}, par ${item.Code_Sal}.`;
    modalContent.appendChild(div);

    item.reponses.forEach((reponse) => {
        if (reponse.Type === 'État') {
            etat = true;
        }
    });

    if (etat) {
        div = document.createElement('div');
        div.innerHTML = `<strong>Actions effectuées :</strong>`;
        innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        item.reponses.forEach((reponse) => {
            if (reponse.Type === 'État') {
                actionDiv = document.createElement('div');
                actionDiv.classList.add('actions');
                actionDiv.classList.add('done');
                actionDiv.innerHTML = reponse.Question;
                innerDiv.appendChild(actionDiv);
            }
        });
        div.appendChild(innerDiv);
        modalContent.appendChild(div);
    }

    item.reponses.forEach((reponse) => {
        if (reponse.Type === 'À Faire') {
            aFaire = true;
        }
    });

    if (aFaire) {
        let rdv = 'À Faire';

        if (item.AFaire_Date && item.AFaire_Heure) {
            rdv = `À Faire le ${formattedDate.format(new Date(item.AFaire_Date))} à ${item.AFaire_Heure.slice(0, 5)}`;
        }

        div = document.createElement('div');
        div.innerHTML = `<strong>${rdv} :</strong>`;
        innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        item.reponses.forEach((reponse) => {
            if (reponse.Type === `À Faire`) {
                actionDiv = document.createElement('div');
                actionDiv.classList.add('actions');
                actionDiv.classList.add('todo');
                actionDiv.innerHTML = reponse.Question;
                innerDiv.appendChild(actionDiv);
            }
        })
        div.appendChild(innerDiv);
        modalContent.appendChild(div)
    }

    if (item.Comm_Detail) {
        div = document.createElement('div');
        div.innerHTML = `<strong>Commentaire :</strong>`;
        innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        innerDiv.innerHTML = item.Comm_Detail;
        div.appendChild(innerDiv);
        modalContent.appendChild(div);
    }

    if (item.Date_RDV) {
        let valid = '';
        if (item.Valid_RDV === 'O') {
            valid = '<span class="valid">Validé</span>';
        } else {
            valid = '<span class="not-valid">Non validé</span>';
        }

        div = document.createElement('div');
        div.innerHTML = `<strong>Rendez-vous :</strong>`;
        innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        innerDiv.innerHTML = `${valid}Le ${formattedDate.format(new Date(item.Date_RDV))} à ${item.Heure_RDV.slice(0, 5)}`;
        div.appendChild(innerDiv);
        modalContent.appendChild(div);
    }
}

function closeModal() {
    modal.classList.add('hidden');

    modalContent.innerHTML = '';
}

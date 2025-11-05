export function initPlanning() {
    const infosDiv = document.getElementById('day-info');
    const planningContainer = document.querySelector('.planning-container');

    let draggedElement = null;

    function getTransitionDuration(el) {
        const style = window.getComputedStyle(el);
        const duration = style.transitionDuration;

        if (duration.includes('ms')) {
            return parseFloat(duration);
        } else if (duration.includes('s')) {
            return parseFloat(duration) * 1000;
        }
        return 0;
    }

    function removeRDVDetail() {
        const existing = document.querySelector('.detail-event');

        if (existing) {
            const content = existing.querySelector('.content');
            content.style.maxHeight = '0px';

            const duration = getTransitionDuration(content);

            existing.addEventListener('transitionend', () => existing.remove(), {once: true});
            return duration;
        }

        return 0;
    }

    function showRDVDetail(item, heure, heureEnr) {
        const delay = removeRDVDetail();

        setTimeout(() => {
            const article = document.createElement('article');
            article.className = 'detail-event';

            const h2 = document.createElement('h2');
            h2.textContent = `Tâches du dossier #${item.NumInt} à ${heure}`;

            const div = document.createElement('div');
            div.className = 'content';

            const ul = document.createElement('ul');

            if (item.type === 'rdv') {
                createRDVDetail(item, heure, heureEnr, ul);
            } else {
                createTacheDetail(item, heure, heureEnr, ul);
            }

            div.appendChild(ul);
            article.append(h2, div);
            planningContainer.appendChild(article);

            div.style.maxHeight = '0px';

            requestAnimationFrame(() => div.style.maxHeight = div.scrollHeight + 'px');
        }, delay);
    }

    function createTacheDetail(item, heure, heureEnr, ul) {
        ul.append(
            createLI(`<strong>Enregistré par :</strong> ${item.Code_Sal} <strong>À :</strong> ${heureEnr}`),
            createLI(`<strong>Technicien en charge des prochaines tâches :</strong> ${item.Tech_Affecte || 'N/A'}`),
            createLI(`<strong>Client :</strong> ${item.Nom_Cli || 'N/A'}`)
        );

        const liTaches = createLI('<strong>Tâches :</strong>');
        const div = document.createElement('div');
        div.className = 'event-dossier-detail';
        const innerDiv = document.createElement('div');
        innerDiv.className = 'actions-container';
        item.taches.forEach(tache => {
            const actionDiv = document.createElement('div');
            actionDiv.classList.add('action');
            actionDiv.classList.add('todo');
            actionDiv.innerHTML = tache.Question;
            innerDiv.appendChild(actionDiv);
        });
        div.appendChild(innerDiv);
        liTaches.append(div);
        ul.append(liTaches);
    }

    function createRDVDetail(item, heure, heureEnr, ul) {
        ul.append(
            createLI(`<strong>Pris par :</strong> ${item.Code_Sal} <strong>À :</strong> ${heureEnr} ${item.Valide === 'O' ? '<div class="valid">Validé</div>' : '<div class="not-valid">Non validé</div>'}`),
            createLI(`<strong>Technicien affecté :</strong> ${item.Tech_RDV || 'N/A'}`),
            createLI(`<strong>Client :</strong> ${item.Nom_Cli || 'N/A'}`),
            createLI(`<strong>Adresse :</strong> ${item.Adresse_Cli || ''}, ${item.CP_Cli || ''} ${item.Ville_Cli || ''}`),
            createLI(`<strong>Contact :</strong> ${item.Mail_Cli || '—'} / ${item.Num_Tel_Cli || '—'}`),
            createLI(`<strong>Marque :</strong> ${item.Marque || '—'}`),
            createLI(`<strong>Type d'appareil :</strong> ${item.Type_App || '—'}`),
        );
    }

    function createLI(innerHTML) {
        const li = document.createElement('li');
        li.innerHTML = innerHTML;
        return li;
    }

    async function fetchDayData(date) {
        infosDiv.style.maxHeight = '0px';

        try {
            const response = await fetch(`/ajax/jour/${date}`);
            const data = await response.json();
            const delay = getTransitionDuration(infosDiv);

            setTimeout(() => {
                displayDayData(data, date);
            }, delay);
        } catch (err) {
            console.error('Erreur de chargement du jour', err);
            infosDiv.innerHTML = '<p>Erreur de chargement.</p>';
            infosDiv.style.maxHeight = infosDiv.scrollHeight + 'px';
        }
    }

    function displayDayData(data, date) {
        infosDiv.innerHTML = '';

        if (!data.length) {
            infosDiv.innerHTML = "<p>Aucun élément planifié ce jour.</p>";
        } else {
            const formattedDate = new Intl.DateTimeFormat('fr-FR', {
                day: 'numeric', month: 'long', year: 'numeric'
            }).format(new Date(date));

            const h3 = document.createElement('h3');
            h3.textContent = `Journée du ${formattedDate}`;

            let dayRdv = false;
            data.forEach(item => {
                if (item.type === 'rdv') dayRdv = true;
            });

            if (dayRdv) {
                const mapBtn = document.createElement('button');
                mapBtn.id = 'openMapBtn';
                mapBtn.type = 'button';
                mapBtn.className = 'btn-map';
                mapBtn.textContent = '🗺️';
                mapBtn.setAttribute('data-tooltip', "Vue carte");
                mapBtn.addEventListener('mouseenter', (e) => {
                    const rect = e.target.getBoundingClientRect();
                    e.target.style.setProperty('--tooltip-top', `${rect.top}px`);
                    e.target.style.setProperty('--tooltip-left', `${rect.left + rect.width / 2}px`);
                });
                let params = new URLSearchParams(document.location.search);
                let numInt = params.get("numInt");
                mapBtn.addEventListener('click', () => {
                    window.open(`/carte?numInt=${numInt}&date=${date}`, 'CarteRDV', 'width=1000,height=700');
                });
                h3.appendChild(mapBtn);
            }
            infosDiv.appendChild(h3);

            const calendar = document.createElement('div');
            calendar.className = 'planning-calendar';

            const div = document.createElement('div');
            div.className = 'calendar-technicien';
            div.innerHTML = '<p>Tech</p>';
            calendar.appendChild(div);

            for (let i = 9; i < 18; i++) {
                for (let j = 0; j <= 30; j += 30) {
                    let hour = `${String(i).padStart(2, '0')}:${String(j).padStart(2, '0')}`;
                    const div = document.createElement('div');
                    div.className = 'horaire';
                    div.innerHTML = `<p>${hour}</p>`;
                    calendar.appendChild(div);
                }
            }

            const grouped = {};
            data.forEach(item => {
                const tech = item.Tech_Affecte ?? item.Tech_RDV ?? 'Inconnu';
                if (!grouped[tech]) grouped[tech] = [];
                grouped[tech].push(item);
            });

            Object.entries(grouped).forEach(([tech, items]) => {
                const divTech = document.createElement('div');
                divTech.className = 'calendar-technicien';
                divTech.innerHTML = `<p>${tech}</p>`;
                calendar.appendChild(divTech);

                const horaireSlots = {};
                for (let i = 9; i < 18; i++) {
                    for (let j = 0; j <= 30; j += 30) {
                        const hour = `${String(i).padStart(2, '0')}:${String(j).padStart(2, '0')}`;
                        const divToDo = document.createElement('div');
                        divToDo.className = 'vacant';
                        divToDo.dataset.hour = String(hour) + ':00';
                        divToDo.dataset.tech = String(tech);
                        calendar.appendChild(divToDo);
                        horaireSlots[hour] = divToDo;

                        divToDo.addEventListener('dragover', e => {
                            e.preventDefault();
                            divToDo.classList.add('drag-hover-vacant');
                        });

                        divToDo.addEventListener('dragleave', () => {
                            divToDo.classList.remove('drag-hover-vacant');
                        });

                        divToDo.addEventListener('drop', async (e) => {
                            e.preventDefault();
                            divToDo.classList.remove('drag-hover-vacant');

                            const hasElement = divToDo.querySelector('.rdv-draggable, .tache-draggable');
                            if (hasElement) {
                                console.warn('Ce créneau est déjà occupé.');
                                return;
                            }

                            if (!draggedElement) return;

                            const itemData = draggedElement.dataset.item ? JSON.parse(draggedElement.dataset.item) : null;
                            if (!itemData) return;

                            const newHour = divToDo.dataset.hour;
                            const newTech = divToDo.dataset.tech;
                            const numIntClean = String(itemData.NumInt).replace(/[\[\]]/g, '');

                            divToDo.appendChild(draggedElement);

                            try {
                                const response = await fetch('/ajax/update-heure', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        type: itemData.type,
                                        numInt: numIntClean,
                                        prevHeure: itemData.heure,
                                        heure: newHour,
                                        date: itemData.date,
                                        technicien: newTech
                                    })
                                });

                                const result = await response.json();
                                if (!response.ok) throw new Error(result.message || 'Erreur serveur');
                            } catch (err) {
                                alert('Erreur lors de la mise à jour du planning.');
                            }
                        });
                    }
                }

                items.forEach(item => {
                    const slot = horaireSlots[String(item.heure).slice(0, 5)];
                    if (!slot) return;

                    const draggable = document.createElement('div');
                    draggable.className = item.type === 'rdv' ? 'rdv-draggable' : 'tache-draggable';
                    draggable.draggable = true;
                    draggable.dataset.item = JSON.stringify(item);

                    const rdv = document.createElement('div');
                    const info = document.createElement('span');
                    const heure = item.heure?.slice(0, 5) || '--:--';
                    const heureEnr = item.Heure_Enrg?.slice(0, 5) || '--:--';

                    info.className = 'info-rdv';
                    info.textContent = 'i';
                    info.addEventListener('click', () => showRDVDetail(item, heure, heureEnr));

                    rdv.className = item.type === 'rdv' ? 'rdv' : 'tache';
                    rdv.appendChild(info);
                    draggable.appendChild(rdv);

                    draggable.addEventListener('dragstart', e => {
                        draggedElement = draggable;
                        e.dataTransfer.effectAllowed = "move";
                    });
                    draggable.addEventListener('dragend', () => draggedElement = null);

                    slot.appendChild(draggable);
                });
            });

            infosDiv.appendChild(calendar);
        }

        infosDiv.style.maxHeight = infosDiv.scrollHeight + 'px';
    }


    return {showRDVDetail, removeRDVDetail, fetchDayData};
}

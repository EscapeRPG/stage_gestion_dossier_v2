export function initCalendar(fetchDayData, removeRDVDetail) {
    let currentDragType = null;
    let currentButton = null;

    const monthNames = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    const shortMonthNames = [
        'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui', 'Juil', 'Aou', 'Sep', 'Oct', 'Nov', 'Déc'
    ];

    const yearDisplay = document.getElementById('yearDisplay');
    const monthDisplay = document.getElementById('monthDisplay');
    const daysContainer = document.getElementById('daysContainer');
    const timeSlotsContainer = document.getElementById('timeSlots');
    const inputDate = document.getElementById('dateRDV');
    const inputTime = document.getElementById('timeRDV');
    const inputDateToDo = document.getElementById('date-a-faire');
    const inputTimeToDo = document.getElementById('heure-a-faire');

    let currentDate = new Date();
    let startDate = getMonday(currentDate);

    function updateHeader() {
        yearDisplay.textContent = currentDate.getFullYear();
        monthDisplay.textContent = monthNames[currentDate.getMonth()];
        startDate = new Date(currentDate);
        renderDays();
    }

    function renderDays() {
        daysContainer.innerHTML = '';
        for (let i = 0; i < 14; i++) {
            const d = new Date(startDate);
            d.setDate(startDate.getDate() + i);

            const button = document.createElement('button');
            button.classList.add('day');
            button.draggable = true;
            button.dataset.date = d.toISOString().split('T')[0];
            button.innerHTML = `
                ${d.toLocaleDateString('fr-FR', {weekday: 'short'}).replace('.', '')}
                <span class="date">${d.getDate()}</span>
                <br><br>
                ${shortMonthNames[d.getMonth()]}
            `;

            if (d.getFullYear() === new Date().getFullYear() &&
                d.getMonth() === new Date().getMonth() &&
                d.getDate() === new Date().getDate()) {
                button.classList.add('today');
            }

            if (d.getDay() === 6 || d.getDay() === 0) button.classList.add('weekend');

            button.addEventListener('dragstart', (e) => {
                button.dragging = true;
                currentDragType = 'date';
                currentButton = button;
                e.dataTransfer.setData('type', 'date');
                e.dataTransfer.setData('value', button.dataset.date);
            });

            button.addEventListener('dragend', () => {
                setTimeout(() => button.dragging = false, 0);
            });

            button.addEventListener('click', () => {
                if (!button.dragging) {
                    document.querySelectorAll('.day').forEach(el => el.classList.remove('selected'));
                    button.classList.add('selected');
                    inputDate.value = button.dataset.date;
                    removeRDVDetail();
                    fetchDayData(button.dataset.date);
                }
            });

            daysContainer.appendChild(button);
        }
    }

    function renderTimeSlots() {
        timeSlotsContainer.innerHTML = '';
        for (let h = 9; h < 18; h++) {
            for (let m of [0, 30]) {
                const button = document.createElement('button');
                button.classList.add('time-slot');
                button.draggable = true;

                const timeValue = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
                button.dataset.time = timeValue;
                button.textContent = timeValue;

                const now = new Date();
                if (
                    now.getHours() === h &&
                    Math.floor(now.getMinutes() - m) >= 0 &&
                    Math.floor(now.getMinutes() - m) < 30
                ) {
                    button.classList.add("now");
                }

                button.addEventListener('dragstart', (e) => {
                    button.dragging = true;
                    currentDragType = 'time';
                    currentButton = button;
                    e.dataTransfer.setData('type', 'time');
                    e.dataTransfer.setData('value', timeValue);
                });

                button.addEventListener('dragend', () => setTimeout(() => button.dragging = false, 0));

                button.addEventListener('click', () => {
                    if (!button.dragging) {
                        document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
                        button.classList.add('selected');
                        inputTime.value = timeValue;
                    }
                });

                timeSlotsContainer.appendChild(button);
            }
        }
    }

    // Navigation buttons
    document.getElementById('prevYear').onclick = () => {
        currentDate.setFullYear(currentDate.getFullYear() - 1);
        currentDate.setMonth(0);
        currentDate.setDate(1);
        updateHeader();
    };
    document.getElementById('nextYear').onclick = () => {
        currentDate.setFullYear(currentDate.getFullYear() + 1);
        currentDate.setMonth(0);
        currentDate.setDate(1);
        updateHeader();
    };
    document.getElementById('prevMonth').onclick = () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        currentDate.setDate(1);
        updateHeader();
    };
    document.getElementById('nextMonth').onclick = () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        currentDate.setDate(1);
        updateHeader();
    };
    document.getElementById('prevDay').onclick = () => changeDisplayedDays(-1);
    document.getElementById('nextDay').onclick = () => changeDisplayedDays(1);
    document.getElementById('today-back').onclick = () => {
        currentDate = new Date();
        currentDate.setFullYear(currentDate.getFullYear());
        currentDate.setMonth(currentDate.getMonth());
        currentDate.setDate(currentDate.getDate());
        updateHeader();
    };

    function changeDisplayedDays(offset) {
        startDate.setDate(startDate.getDate() + offset);
        renderDays();
    }

    function getMonday(d) {
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        return new Date(d.setDate(diff));
    }

    [inputDate, inputTime, inputDateToDo, inputTimeToDo].forEach(input => {
        input.addEventListener('dragover', (e) => {
            if ((currentDragType === 'date' && input.type === 'date') ||
                (currentDragType === 'time' && input.type === 'select-one')) {
                e.preventDefault();
            } else {
                input.classList.add('drag-invalid');
            }
        });
        input.addEventListener('dragenter', () => input.classList.add('drag-hover'));
        input.addEventListener('dragleave', () => {
            input.classList.remove('drag-hover');
            input.classList.remove('drag-invalid');
        });
        input.addEventListener('drop', (e) => {
            e.preventDefault();
            const type = e.dataTransfer.getData('type');
            const value = e.dataTransfer.getData('value');

            const valid = (input.type === 'date' && type === 'date') ||
                (input.type === 'select-one' && type === 'time');

            if (valid) {
                input.value = value;

                if (input === inputDate) {
                    fetchDayData(value);
                }

                if (input === inputDate || input === inputTime) {
                    if (input === inputDate) {
                        document.querySelectorAll('.day.selected').forEach(el => el.classList.remove('selected'));
                    } else {
                        document.querySelectorAll('.time-slot.selected').forEach(el => el.classList.remove('selected'));
                    }
                    currentButton.classList.add('selected');
                }
            } else {
                input.classList.add('drag-invalid');
                setTimeout(() => input.classList.remove('drag-invalid'), 500);
            }

            input.classList.remove('drag-hover');
        });
        input.addEventListener('change', (e) => {
            if (input === inputDate) {
                document.querySelectorAll('.day.selected').forEach(el => el.classList.remove('selected'));

                if (input.value !== '') {
                    fetchDayData(input.value);

                    const day = document.querySelector(`button[data-date="${input.value}"]`);

                    if (day) day.classList.add('selected');
                }
            } else if (input === inputTime) {
                document.querySelectorAll('.time-slot.selected').forEach(el => el.classList.remove('selected'));

                if (input.value !== '') {
                    document.querySelector(`button[data-time="${input.value}"]`).classList.add('selected');
                }
            }
        })
    });

    updateHeader();
    renderTimeSlots();

    return {renderDays, renderTimeSlots, currentDragType};
}

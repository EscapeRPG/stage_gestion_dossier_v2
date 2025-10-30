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

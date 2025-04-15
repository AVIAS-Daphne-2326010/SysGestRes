import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', () => {
    new Calendar(document.getElementById('calendar'), {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth'
    }).render();
});
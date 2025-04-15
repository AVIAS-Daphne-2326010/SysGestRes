import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

document.addEventListener('DOMContentLoaded', () => {
    const calendarEl = document.getElementById('calendar');

    if (calendarEl) {
        const events = JSON.parse(calendarEl.dataset.events);

        const calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            locale: frLocale,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: events,
            eventClick: function(info) {
                if (info.event.extendedProps.reserved) {
                    alert('Déjà réservé: ' + info.event.title);
                } else {
                    window.location.href = '/reservation/new?start=' + info.event.startStr;
                }
            }
        });

        calendar.render();
    }
});

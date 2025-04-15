// ==============================
// Imports nécessaires à FullCalendar
// ==============================

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

// ==============================
// Initialisation une fois le DOM chargé
// ==============================

document.addEventListener('DOMContentLoaded', () => {
    // Récupération de l'élément contenant le calendrier
    const calendarEl = document.getElementById('calendar');

    // Vérifie si l'élément existe bien dans le DOM
    if (calendarEl) {
        // Récupération des événements JSON injectés via Twig
        const events = JSON.parse(calendarEl.dataset.events);

        // Création de l'instance FullCalendar
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

            // Lorsqu'on clique sur un événement
            eventClick: function(info) {
                if (info.event.extendedProps.reserved) {
                    alert('Déjà réservé: ' + info.event.title);
                } else {
                    // Redirige vers le formulaire de réservation
                    window.location.href = '/reservation/create?start=' + info.event.startStr;
                }
            }
        });

        // Affichage du calendrier
        calendar.render();
    }
});

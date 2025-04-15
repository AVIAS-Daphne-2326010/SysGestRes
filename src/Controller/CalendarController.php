<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'calendrier')]
    public function calendrier(): Response
    {
        $events = [
            [
                'title' => 'Réunion équipe',
                'start' => (new \DateTime('+1 day 09:00:00'))->format('Y-m-d\TH:i:s'),
                'end' => (new \DateTime('+1 day 10:30:00'))->format('Y-m-d\TH:i:s'),
                'color' => '#3788d8'
            ],
            [
                'title' => 'Entretien client',
                'start' => (new \DateTime('+2 day 14:00:00'))->format('Y-m-d\TH:i:s'),
                'end' => (new \DateTime('+2 day 15:30:00'))->format('Y-m-d\TH:i:s'),
                'color' => '#28a745'
            ],
            [
                'title' => 'Créneau disponible',
                'start' => (new \DateTime('+3 day 11:00:00'))->format('Y-m-d\TH:i:s'),
                'end' => (new \DateTime('+3 day 12:00:00'))->format('Y-m-d\TH:i:s'),
                'color' => '#ffc107',
                'textColor' => '#000'
            ]
        ];

        return $this->render('calendrier/calendrier.html.twig', [
            'events' => $events
        ]);
    }
}
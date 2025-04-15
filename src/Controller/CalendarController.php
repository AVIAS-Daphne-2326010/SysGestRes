<?php

namespace App\Controller;

use App\Entity\Creneau;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'calendrier')]
public function calendrier(EntityManagerInterface $entityManager): Response
{
    $creneaux = $entityManager->getRepository(Creneau::class)->findAll();
    
    $events = [];
    foreach ($creneaux as $creneau) {
        $events[] = [
            'title' => $creneau->getRessource()->getName() . ' - ' . $creneau->getDateDebut()->format('H:i'),
            'start' => $creneau->getDateDebut()->format('Y-m-d\TH:i:s'),
            'end' => $creneau->getDateFin()->format('Y-m-d\TH:i:s'),
            'color' => $creneau->getReservation() ? '#FF0000' : '#28a745',
            'extendedProps' => [
                'creneauId' => $creneau->getId(),
                'reserved' => $creneau->getReservation() !== null
            ]
        ];
    }

    // Debug : vérifiez les données envoyées
    dump($events);
    
    return $this->render('calendrier/calendrier.html.twig', [
        'events' => $events
    ]);
}
}
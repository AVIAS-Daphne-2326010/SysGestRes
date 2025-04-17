<?php

namespace App\Controller;

use App\Entity\Creneau;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    #[Route('/calendrier', name: 'calendrier')]
    public function calendrier(): Response
    {
        return $this->render('calendrier/calendrier.html.twig');
    }

    #[Route('/api/creneaux', name: 'api_creneaux')]
    public function apiCreneaux(EntityManagerInterface $em, LoggerInterface $logger): JsonResponse
    {
        try {
            $creneaux = $em->getRepository(Creneau::class)
                ->createQueryBuilder('c')
                ->leftJoin('c.reservation', 'r')
                ->join('c.ressource', 'res')
                ->addSelect('r', 'res')
                ->getQuery()
                ->getResult();

            $events = array_map(function($creneau) {
                return [
                    'id' => $creneau->getId(),
                    'title' => $creneau->getRessource()->getName(),
                    'start' => $creneau->getDateDebut()->format('Y-m-d\TH:i:s'),
                    'end' => $creneau->getDateFin()->format('Y-m-d\TH:i:s'),
                    'color' => $creneau->getReservation() ? '#FF0000' : '#28a745',
                    'extendedProps' => [
                        'creneauId' => $creneau->getId(),
                        'reserved' => $creneau->getReservation() !== null
                    ]
                ];
            }, $creneaux);

            return new JsonResponse($events);
            
        } catch (\Exception $e) {
            $logger->error('API Creneaux Error: ' . $e->getMessage());
            return new JsonResponse(
                ['error' => 'Erreur lors de la récupération des créneaux'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
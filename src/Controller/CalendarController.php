<?php

namespace App\Controller;

use App\Entity\Creneau;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CalendarController extends AbstractController
{
    /**
     * Affiche la page principale du calendrier
     */
    #[Route('/calendrier', name: 'calendrier')]
    public function calendrier(): Response
    {
        return $this->render('calendrier/calendrier.html.twig');
    }

    /**
     * Endpoint API pour récupérer les créneaux au format JSON
     * Utilisé par FullCalendar et la liste des créneaux disponibles
     */
    #[Route('/api/creneaux', name: 'api_creneaux')]
    public function apiCreneaux(Request $request, EntityManagerInterface $em, LoggerInterface $logger): JsonResponse
    {
        try {
            // Récupération des créneaux avec leurs relations
            $creneaux = $em->getRepository(Creneau::class)
                ->createQueryBuilder('c')
                ->leftJoin('c.reservation', 'r')  // Jointure gauche pour inclure même les créneaux sans réservation
                ->join('c.ressource', 'res')      // Jointure avec la ressource
                ->addSelect('r', 'res')           // Sélection des entités jointes
                ->getQuery()
                ->getResult();
            
            // Formatage des données pour FullCalendar
            $events = array_map(function($creneau) {
                return [
                    'title' => $creneau->getRessource()->getName() . ' - ' . $creneau->getDateDebut()->format('H:i'),
                    'start' => $creneau->getDateDebut()->format('Y-m-d\TH:i:s'),
                    'end' => $creneau->getDateFin()->format('Y-m-d\TH:i:s'),
                    'color' => $creneau->getReservation() ? '#FF0000' : '#28a745', // Rouge si réservé, vert sinon
                    'extendedProps' => [
                        'creneauId' => $creneau->getId(),
                        'reserved' => $creneau->getReservation() !== null
                    ]
                ];
            }, $creneaux);
            
            return new JsonResponse($events);
            
        } catch (\Exception $e) {
            // Log l'erreur et retourne une réponse d'erreur
            $logger->error('API Creneaux Error: ' . $e->getMessage());
            return new JsonResponse(
                ['error' => 'Une erreur est survenue lors de la récupération des créneaux'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
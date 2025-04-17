<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/reservation/create', name: 'create_reservation')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
    
        if ($request->isXmlHttpRequest()) {
            if ($form->isSubmitted()) {
                if (!$form->isValid()) {
                    $errors = [];
                    foreach ($form->getErrors(true) as $error) {
                        $errors[$error->getOrigin()->getName()] = $error->getMessage();
                    }
                    return $this->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => $errors
                    ], 400);
                }
    
                if ($reservation->getCreneau()->getReservation() !== null) {
                    return $this->json([
                        'success' => false, 
                        'message' => 'Ce créneau est déjà réservé.'
                    ], 400);
                }
    
                $reservation->setUtilisateur($this->getUser());
                $entityManager->persist($reservation);
                $entityManager->flush();
    
                return $this->json([
                    'success' => true,
                    'message' => 'Réservation créée avec succès!',
                    'reservation' => [
                        'id' => $reservation->getId(),
                        'date' => $reservation->getDateReservation()->format('d/m/Y H:i'),
                        'creneau' => $reservation->getCreneau()->getDateDebut()->format('H:i').' - '.$reservation->getCreneau()->getDateFin()->format('H:i')
                    ],
                    'wasEmpty' => false
                ]);
            }
    
            return $this->json([
                'html' => $this->renderView('reservation/_form.html.twig', [
                    'form' => $form->createView()
                ])
            ]);
        }
    
        return $this->render('reservation/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/reservations', name: 'reservation_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findBy([], ['dateReservation' => 'DESC']);
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('reservation/_list.html.twig', [
                    'reservations' => $reservations,
                ])
            ]);
        }
        
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }
    

    #[Route('/reservation/{id}/annuler', name: 'annuler_reservation', methods: ['POST'])]
    public function annuler(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);
        
        if (!$reservation) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Réservation non trouvée'], 404);
            }
            throw $this->createNotFoundException('Réservation non trouvée');
        }
    
        if ($reservation->getUtilisateur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
            }
            throw $this->createAccessDeniedException();
        }
        
        $entityManager->remove($reservation);
        $entityManager->flush();
    
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Réservation annulée avec succès.',
                'isEmpty' => count($entityManager->getRepository(Reservation::class)->findAll()) === 0
            ]);
        }
        
        $this->addFlash('success', 'Réservation annulée avec succès.');
        return $this->redirectToRoute('reservation_index');
    }

    #[Route('/api/reservations', name: 'api_reservations')]
    public function getReservationsApi(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $reservations = $entityManager->getRepository(Reservation::class)->findAll();
        } else {
            $reservations = $entityManager->getRepository(Reservation::class)->findBy(['utilisateur' => $user]);
        }

        $data = [];
        foreach ($reservations as $reservation) {
            $data[] = [
                'id' => $reservation->getId(),
                'dateReservation' => $reservation->getDateReservation()->format('Y-m-d H:i'),
                'utilisateur' => $reservation->getUtilisateur()->getNom(),
                'creneauDebut' => $reservation->getCreneau()->getDateDebut()->format('Y-m-d H:i'),
                'creneauFin' => $reservation->getCreneau()->getDateFin()->format('H:i'),
            ];
        }

        return new JsonResponse($data);
    }
}
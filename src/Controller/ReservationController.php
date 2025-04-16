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

        if ($form->isSubmitted() && $form->isValid()) {
            $creneau = $reservation->getCreneau();
            if ($creneau->getReservation() !== null) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['success' => false, 'message' => 'Ce créneau est déjà réservé.']);
                }
                $this->addFlash('error', 'Ce créneau est déjà réservé.');
                return $this->redirectToRoute('create_reservation');
            }

            $entityManager->persist($reservation);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true, 'message' => 'Réservation créée avec succès!']);
            }
            
            $this->addFlash('success', 'Réservation créée avec succès!');
            return $this->redirectToRoute('reservation_index');
        }

        // Retourne uniquement le HTML du formulaire pour les requêtes AJAX
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('reservation/_form.html.twig', [
                    'form' => $form->createView(),
                ])
            ]);
        }

        return $this->render('reservation/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reservation', name: 'reservation_index')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $reservations = $entityManager->getRepository(Reservation::class)->findAll();
        } else {
            $reservations = $entityManager->getRepository(Reservation::class)->findBy(['utilisateur' => $user]);
        }

        // Retourne uniquement le HTML du tableau pour les requêtes AJAX
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
            return new JsonResponse(['success' => true, 'message' => 'Réservation annulée avec succès.']);
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
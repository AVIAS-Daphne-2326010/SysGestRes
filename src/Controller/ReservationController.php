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
    // Route pour créer une nouvelle réservation
    #[Route('/reservation/create', name: 'create_reservation')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
    
        // Traitement de la requête AJAX (pour la soumission du formulaire)
        if ($request->isXmlHttpRequest()) {
            if ($form->isSubmitted()) {
                if (!$form->isValid()) {
                    // Gestion des erreurs de validation
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
        
                // Vérification si le créneau est déjà réservé
                if ($reservation->getCreneau()->getReservation() !== null) {
                    return $this->json([
                        'success' => false, 
                        'message' => 'Ce créneau est déjà réservé.'
                    ], 400);
                }
        
                // Attribution de l'utilisateur à la réservation
                $reservation->setUtilisateur($this->getUser());
                $entityManager->persist($reservation);
                $entityManager->flush();
        
                // Retourne la ligne de réservation à insérer dans la liste
                return $this->json([
                    'success' => true,
                    'message' => 'Réservation créée avec succès!',
                    'reservationRow' => $this->renderView('reservation/_row.html.twig', [
                        'reservation' => $reservation
                    ]),
                    'wasEmpty' => false
                ]);
            }
        
            // Retourne le formulaire sous forme HTML en cas de requête AJAX
            return $this->json([
                'html' => $this->renderView('reservation/_form.html.twig', [
                    'form' => $form->createView()
                ])
            ]);
        }
        
        // Rendu de la page de création de réservation
        return $this->render('reservation/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // Route pour afficher la liste des réservations
    #[Route('/reservations', name: 'reservation_index')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération des réservations triées par date
        $reservations = $entityManager->getRepository(Reservation::class)->findBy([], ['dateReservation' => 'DESC']);
        
        // Traitement de la requête AJAX pour afficher la liste des réservations
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html' => $this->renderView('reservation/_list.html.twig', [
                    'reservations' => $reservations,
                ])
            ]);
        }
        
        // Rendu de la page avec les réservations
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }
    
    // Route pour annuler une réservation
    #[Route('/reservation/{id}/annuler', name: 'annuler_reservation', methods: ['POST'])]
    public function annuler(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Récupération de la réservation
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);
        
        // Gestion du cas où la réservation n'existe pas
        if (!$reservation) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Réservation non trouvée'], 404);
            }
            throw $this->createNotFoundException('Réservation non trouvée');
        }
    
        // Vérification des droits de l'utilisateur
        if ($reservation->getUtilisateur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Accès refusé'], 403);
            }
            throw $this->createAccessDeniedException();
        }
        
        // Suppression de la réservation
        $entityManager->remove($reservation);
        $entityManager->flush();
    
         // Retourne une réponse JSON après suppression
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Réservation annulée avec succès.',
                'isEmpty' => count($entityManager->getRepository(Reservation::class)->findAll()) === 0
            ]);
        }
        
        // Message flash et redirection vers la liste des réservations
        $this->addFlash('success', 'Réservation annulée avec succès.');
        return $this->redirectToRoute('reservation_index');
    }

    // Route pour récupérer les réservations via une API
    #[Route('/api/reservations', name: 'api_reservations')]
    public function getReservationsApi(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        
        // Récupération des réservations selon le rôle de l'utilisateur
        if ($this->isGranted('ROLE_ADMIN')) {
            $reservations = $entityManager->getRepository(Reservation::class)->findAll();
        } else {
            $reservations = $entityManager->getRepository(Reservation::class)->findBy(['utilisateur' => $user]);
        }

        // Formatage des données des réservations pour la réponse JSON
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
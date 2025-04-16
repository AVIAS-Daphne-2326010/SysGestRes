<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    /**
     * Crée une nouvelle réservation
     */
    #[Route('/reservation/create', name: 'create_reservation')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée une nouvelle instance de Reservation
        $reservation = new Reservation();
        
        // Crée le formulaire associé
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si le créneau est déjà réservé
            $creneau = $reservation->getCreneau();
            if ($creneau->getReservation() !== null) {
                $this->addFlash('error', 'Ce créneau est déjà réservé.');
                return $this->redirectToRoute('create_reservation');
            }

            // Enregistre la réservation
            $entityManager->persist($reservation);
            $entityManager->flush();

            // Message de succès et redirection
            $this->addFlash('success', 'Réservation créée avec succès!');
            return $this->redirectToRoute('reservation_index');
        }

        // Affiche le formulaire de création
        return $this->render('reservation/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Affiche la liste des réservations
     */
    #[Route('/reservation', name: 'reservation_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();
        
        // Si admin, montre toutes les réservations, sinon seulement celles de l'utilisateur
        if ($this->isGranted('ROLE_ADMIN')) {
            $reservations = $entityManager->getRepository(Reservation::class)->findAll();
        } else {
            $reservations = $entityManager->getRepository(Reservation::class)->findBy(['utilisateur' => $user]);
        }

        // Affiche la liste
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Annule une réservation
     */
    #[Route('/reservation/{id}/annuler', name: 'annuler_reservation')]
    public function annuler(int $id, EntityManagerInterface $entityManager): Response
    {
        // Trouve la réservation par son ID
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);
        
        // Vérifie si la réservation existe
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }
    
        // Vérifie les permissions (soit propriétaire, soit admin)
        if ($reservation->getUtilisateur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
        
        // Supprime la réservation
        $entityManager->remove($reservation);
        $entityManager->flush();
    
        // Message de succès et redirection
        $this->addFlash('success', 'Réservation annulée avec succès.');
        return $this->redirectToRoute('reservation_index');
    }
}
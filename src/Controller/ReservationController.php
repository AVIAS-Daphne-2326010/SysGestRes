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
    #[Route('/reservation/create', name: 'create_reservation')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();

        $form = $this->createForm(ReservationType::class, $reservation);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $creneau = $reservation->getCreneau();
            if ($creneau->getReservation() !== null) {
                $this->addFlash('error', 'Ce créneau est déjà réservé.');
                return $this->redirectToRoute('create_reservation');
            }

            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('accueil');
        }

        return $this->render('reservation/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/reservation', name: 'reservation_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $reservations = $entityManager->getRepository(Reservation::class)->findAll();
        } else {
            $reservations = $entityManager->getRepository(Reservation::class)->findBy(['utilisateur' => $user]);
        }

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/reservation/{id}/annuler', name: 'annuler_reservation')]
    public function annuler(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette réservation.');
        }
        
        $entityManager->remove($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Réservation annulée avec succès.');

        return $this->redirectToRoute('list_reservations');
    }

}

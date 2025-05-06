<?php

namespace App\Controller;

use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/bookings')]
class AdminBookingController extends AbstractController
{
    // Affiche la liste des réservations
    #[Route('/', name: 'admin_bookings', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer toutes les réservations de la base
        $bookings = $em->getRepository(Booking::class)->findAll();
        
        return $this->render('admin/bookings/index.html.twig', [
            'bookings' => $bookings,
        ]);
        
    }

    // Afficher les détails d'une réservation spécifique
    #[Route('/{id}', name: 'admin_booking_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $booking = $em->getRepository(Booking::class)->find($id);

        if (!$booking) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        return $this->render('admin/bookings/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    // Supprimer une réservation spécifique
    #[Route('/{id}/delete', name: 'admin_booking_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $booking = $em->getRepository(Booking::class)->find($id);

        if (!$booking) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        // Supprimer la réservation
        $em->remove($booking);
        $em->flush();

        // Rediriger vers la liste des réservations
        $this->addFlash('success', 'Réservation supprimée avec succès');
        return $this->redirectToRoute('admin_bookings');
    }
}

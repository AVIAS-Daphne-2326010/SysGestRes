<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Client;
use App\Form\BookingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client')]
class ClientBookingController extends AbstractController
{
    #[Route('/bookings', name: 'client_bookings')]
    public function bookings(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $client = $em->getRepository(Client::class)->findOneBy([
            'userAccount' => $user,
        ]);

        if (!$client) {
            throw $this->createAccessDeniedException("Aucun client associé à cet utilisateur.");
        }

        $qb = $em->createQueryBuilder();
        $qb->select('b')
            ->from(Booking::class, 'b')
            ->join('b.timeslot', 't')
            ->join('t.resource', 'r')
            ->where('r.client = :client')
            ->setParameter('client', $client)
            ->orderBy('b.createdAt', 'DESC');

        $bookings = $qb->getQuery()->getResult();

        return $this->render('client/booking/bookings.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('/booking/{id}/edit', name: 'booking_edit')]
    public function edit(Booking $booking, Request $request, EntityManagerInterface $em): Response
    {
        // Vérification que la réservation n'est pas annulée
        if ($booking->getStatus() === 'cancelled') {
            $this->addFlash('warning', 'Impossible de modifier une réservation annulée.');
            return $this->redirectToRoute('client_bookings');
        }

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Réservation modifiée avec succès.');
            return $this->redirectToRoute('client_bookings');
        }

        return $this->render('client/booking/edit.html.twig', [
            'form' => $form->createView(),
            'booking' => $booking,
        ]);
    }

    #[Route('/booking/{id}/delete', name: 'booking_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $em): Response
    {
        if ($booking->getStatus() === 'cancelled') {
            $this->addFlash('warning', 'Impossible de supprimer une réservation annulée.');
            return $this->redirectToRoute('client_bookings');
        }

        if ($this->isCsrfTokenValid('delete' . $booking->getId(), $request->request->get('_token'))) {
            $em->remove($booking);
            $em->flush();
            $this->addFlash('success', 'Réservation supprimée.');
        }

        return $this->redirectToRoute('client_bookings');
    }
}

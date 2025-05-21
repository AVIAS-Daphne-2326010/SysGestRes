<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\BookingHistory;
use App\Entity\UserAccount;
use App\Form\BookingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/bookings')]
class AdminBookingController extends AbstractController
{
    #[Route('/', name: 'admin_bookings', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $bookings = $em->getRepository(Booking::class)->findAll();

        return $this->render('admin/bookings/index.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('/{id}', name: 'admin_booking_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $booking = $em->getRepository(Booking::class)->find($id);

        if (!$booking) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        return $this->render('admin/bookings/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_booking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $booking = $em->getRepository(Booking::class)->find($id);

        if (!$booking) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        if ($booking->getStatus() === 'cancelled') {
            $this->addFlash('warning', 'Impossible de modifier une réservation annulée.');
            return $this->redirectToRoute('admin_bookings');
        }

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            /** @var UserAccount $user */
            $user = $this->getUser();

            $log = new BookingHistory();
            $log->setStatus('Modification de réservation')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setBooking($booking);

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Réservation modifiée avec succès.');
            return $this->redirectToRoute('admin_bookings');
        }

        return $this->render('admin/bookings/edit.html.twig', [
            'form' => $form->createView(),
            'booking' => $booking,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_booking_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $booking = $em->getRepository(Booking::class)->find($id);

        if (!$booking) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        if ($booking->getStatus() === 'cancelled') {
            $this->addFlash('warning', 'Cette réservation est déjà annulée.');
            return $this->redirectToRoute('admin_bookings');
        }

        $submittedToken = $request->request->get('_token');

        if ($csrfTokenManager->isTokenValid(new CsrfToken('delete' . $booking->getId(), $submittedToken))) {
            // Annuler la réservation au lieu de la supprimer
            $booking->setStatus('cancelled');
            $booking->setCancelledAt(new \DateTime());

            // Libérer le créneau
            $booking->getTimeslot()?->setIsAvailable(true);

            /** @var UserAccount $user */
            $user = $this->getUser();

            $log = new BookingHistory();
            $log->setStatus('Annulation de réservation')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setBooking($booking);

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Réservation annulée et créneau libéré.');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('admin_bookings');
    }
}

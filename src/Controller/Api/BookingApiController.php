<?php

namespace App\Controller\Api;

use App\Entity\Booking;
use App\Entity\Timeslot;
use App\Entity\UserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/bookings')]
class BookingApiController extends AbstractController
{
    #[Route('', name: 'api_bookings_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $bookings = $entityManager->getRepository(Booking::class)->findAll();

        $data = [];

        foreach ($bookings as $booking) {
            $data[] = [
                'id' => $booking->getId(),
                'title' => 'Réservation',
                'start' => $booking->getTimeslot()->getStartDatetime()->format('Y-m-d\TH:i:s'),
                'end' => $booking->getTimeslot()->getEndDatetime()->format('Y-m-d\TH:i:s'),
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'api_booking_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var UserAccount $user */
        $user = $this->getUser();
        $timeslotId = $request->request->get('timeslotId');
        $comment = $request->request->get('comment'); // ← Ajout ici

        if (!$timeslotId) {
            return new JsonResponse(['error' => 'ID du créneau manquant'], 400);
        }

        $timeslot = $entityManager->getRepository(Timeslot::class)->find($timeslotId);

        if (!$timeslot) {
            return new JsonResponse(['error' => 'Créneau introuvable'], 404);
        }

        if (!$timeslot->isAvailable()) {
            return new JsonResponse(['error' => 'Créneau déjà réservé'], 400);
        }

        $booking = new Booking();
        $booking->setUserAccount($user);
        $booking->setTimeslot($timeslot);
        $booking->setCreatedAt(new \DateTime());
        $booking->setStatus('confirmed');
        $booking->setComment($comment); 

        $timeslot->setIsAvailable(false);

        $entityManager->persist($booking);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Réservation créée avec succès',
            'bookingId' => $booking->getId(),
        ]);
    }

}

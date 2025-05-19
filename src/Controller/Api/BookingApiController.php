<?php

namespace App\Controller\Api;

use App\Entity\Booking;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

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
}

<?php

namespace App\Controller;

use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $bookings = $em->getRepository(Booking::class)->findBy([
            'userAccount' => $user,
        ]);

        return $this->render('client/bookings.html.twig', [
            'bookings' => $bookings,
        ]);
    }
}

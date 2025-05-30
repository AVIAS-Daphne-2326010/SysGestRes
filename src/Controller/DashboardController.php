<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\UserAccount $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $stats = [
            'users' => $em->createQuery('SELECT COUNT(u.id) FROM App\Entity\UserAccount u')->getSingleScalarResult(),
            'clients' => $em->createQuery('SELECT COUNT(c.id) FROM App\Entity\Client c')->getSingleScalarResult(),
            'resources' => $em->createQuery('SELECT COUNT(r.id) FROM App\Entity\Resource r')->getSingleScalarResult(),
            'bookings' => $em->createQuery('SELECT COUNT(b.id) FROM App\Entity\Booking b')->getSingleScalarResult(),
        ];

        return $this->render('admin/dashboard.html.twig', [  
            'prenom' => $user->getFirstName(),
            'stats' => $stats
        ]);
    }

    #[Route('/client/dashboard', name: 'client_dashboard')]
    public function clientDashboard(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\UserAccount $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $client = $user->getClient();

        $bookings = $em->createQuery(
            'SELECT b, t, r, u
            FROM App\Entity\Booking b
            JOIN b.timeslot t
            JOIN t.resource r
            JOIN b.userAccount u
            WHERE r.client = :client
            AND b.cancelledAt IS NULL
            ORDER BY t.startDatetime ASC'
        )
        ->setParameter('client', $client)
        ->setMaxResults(3)
        ->getResult();

        return $this->render('client/dashboard.html.twig', [
            'prenom' => $user->getFirstName(),
            'organization' => $client?->getOrganizationName(),
            'bookings' => $bookings
        ]);
    }

    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function userDashboard(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\UserAccount $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $nextBooking = $em->createQuery(
            'SELECT b FROM App\Entity\Booking b 
             JOIN b.timeslot t 
             WHERE b.userAccount = :user AND t.startDatetime > :now 
             ORDER BY t.startDatetime ASC'
        )
        ->setParameter('user', $user)
        ->setParameter('now', new \DateTime())
        ->setMaxResults(1)
        ->getOneOrNullResult();

        return $this->render('user/dashboard.html.twig', [
            'prenom' => $user->getFirstName(),
            'nextBooking' => $nextBooking
        ]);
    }
}

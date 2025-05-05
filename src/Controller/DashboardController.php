<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    // Route pour le tableau de bord Admin
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
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

        return $this->render('admin/dashboard.html.twig', [  // Correct path to admin dashboard
            'prenom' => $user->getFirstName(),
            'stats' => $stats
        ]);
    }

    // Route pour le tableau de bord Client
    #[Route('/client/dashboard', name: 'client_dashboard')]
    public function clientDashboard(EntityManagerInterface $em): Response
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

        $organization = $user->getClient()?->getOrganizationName(); // relation avec Client

        return $this->render('client/dashboard.html.twig', [  // Correct path to client dashboard
            'prenom' => $user->getFirstName(),
            'organization' => $organization,
            'nextBooking' => $nextBooking
        ]);
    }

    // Route pour le tableau de bord Utilisateur (par défaut)
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

        return $this->render('user/dashboard.html.twig', [  // Correct path to user dashboard
            'prenom' => $user->getFirstName(),
            'nextBooking' => $nextBooking
        ]);
    }
}

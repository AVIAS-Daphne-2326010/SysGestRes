<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\UserAccount $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Si administrateur
        if ($this->isGranted('ROLE_ADMIN')) {
            $stats = [
                'users' => $em->createQuery('SELECT COUNT(u.id) FROM App\Entity\UserAccount u')->getSingleScalarResult(),
                'clients' => $em->createQuery('SELECT COUNT(c.id) FROM App\Entity\Client c')->getSingleScalarResult(),
                'resources' => $em->createQuery('SELECT COUNT(r.id) FROM App\Entity\Resource r')->getSingleScalarResult(),
                'bookings' => $em->createQuery('SELECT COUNT(b.id) FROM App\Entity\Booking b')->getSingleScalarResult(),
            ];

            return $this->render('dashboard/admin.html.twig', [
                'prenom' => $user->getFirstName(),
                'stats' => $stats
            ]);
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

        // Si client
        if ($this->isGranted('ROLE_CLIENT')) {
            $organization = $user->getClient()?->getOrganizationName(); // relation avec Client

            return $this->render('dashboard/client.html.twig', [
                'prenom' => $user->getFirstName(),
                'organization' => $organization,
                'nextBooking' => $nextBooking
            ]);
        }

        // Si simple utilisateur
        return $this->render('dashboard/user.html.twig', [
            'prenom' => $user->getFirstName(),
            'nextBooking' => $nextBooking
        ]);
    }
}

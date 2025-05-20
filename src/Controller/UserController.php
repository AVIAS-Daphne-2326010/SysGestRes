<?php

namespace App\Controller;

use App\Entity\UserAccount;
use App\Entity\Resource;
use App\Form\UserAccountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/profile', name: 'user_profile')]
    public function profile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var UserAccount $user */
        $user = $this->getUser();

        $form = $this->createForm(UserAccountType::class, $user, [
            'is_edit' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/resources', name: 'user_resources')]
    public function resources(EntityManagerInterface $entityManager): Response
    {
        // Requête DQL pour obtenir les types distincts
        $types = $entityManager->createQuery(
            'SELECT DISTINCT r.type FROM App\Entity\Resource r'
        )->getSingleColumnResult();

        return $this->render('user/resource/index.html.twig', [
            'types' => $types,
        ]);
    }

    #[Route('/user/resources/{type}', name: 'user_resources_by_type')]
    public function resourcesByType(string $type, EntityManagerInterface $entityManager): Response
    {
        $resources = $entityManager->getRepository(Resource::class)->findBy(['type' => $type]);

        return $this->render('user/resource/show.html.twig', [
            'type' => $type,
            'resources' => $resources,
        ]);
    }

    #[Route('/user/resource/{id}/calendar', name: 'user_resource_calendar')]
    public function resourceCalendar(int $id, EntityManagerInterface $entityManager): Response
    {
        $resource = $entityManager->getRepository(Resource::class)->find($id);

        if (!$resource) {
            throw $this->createNotFoundException('Ressource non trouvée');
        }

        return $this->render('user/timeslot/calendar.html.twig', [
            'resource' => $resource,
        ]);
    }


    #[Route('/user/calendar', name: 'user_calendar')]
    public function calendar(): Response
    {
        return $this->render('user/calendar.html.twig');
    }

    #[Route('/user/bookings', name: 'user_bookings')]
    public function bookings(): Response
    {
        return $this->render('user/bookings.html.twig');
    }
}

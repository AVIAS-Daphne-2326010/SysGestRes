<?php

namespace App\Controller;

use App\Entity\UserAccount;
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
            // Si un mot de passe est soumis, le mettre à jour
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
    public function resources(): Response
    {
        return $this->render('user/resources.html.twig');
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
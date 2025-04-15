<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Gestion Turbo
            $isTurboRequest = $request->headers->get('Turbo-Frame');

            // Vérification email existant
            if ($entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $user->getEmail()])) {
                $form->get('email')->addError(new FormError('Cet email est déjà utilisé'));
            }

            if ($form->isValid()) {
                $this->processRegistration($user, $form, $userPasswordHasher, $entityManager);
                $this->addFlash('success', 'Inscription réussie !');
                
                return $this->redirectToRoute('login');
            }

            $this->addFlash('error', 'Il y a des erreurs dans le formulaire');
        }

        return $this->renderFormResponse($form, $request);
    }

    private function processRegistration(
        Utilisateur $user,
        $form,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em
    ): void {
        $user->setPassword(
            $hasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );
        $user->setIsAdmin(false);
        $em->persist($user);
        $em->flush();
    }

    private function renderFormResponse($form, Request $request): Response
    {
        $template = 'registration/register.html.twig';
        $status = $form->isSubmitted() && !$form->isValid() ? 422 : 200;

        if ($request->headers->get('Turbo-Frame')) {
            return $this->render($template, ['registrationForm' => $form], new Response(null, $status));
        }

        return $this->render($template, ['registrationForm' => $form->createView()]);
    }
}
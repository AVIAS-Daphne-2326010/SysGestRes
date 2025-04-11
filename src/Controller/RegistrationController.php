<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            if ($form->isValid()) {
                $existingUser = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $user->getEmail()]);
            
                if ($existingUser) {
                    $form->get('email')->addError(new FormError('Cette adresse email est déjà utilisée'));
                } 

                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
        
                $user->setIsAdmin(false);
        
                $entityManager->persist($user);
                $entityManager->flush();
        
                $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('login');
            } else {
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
            }

        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}

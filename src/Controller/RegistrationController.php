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
use Symfony\Component\Validator\ConstraintViolation;

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
            $existingUser = $entityManager->getRepository(Utilisateur::class)
            ->findOneBy(['email' => $user->getEmail()]);

            if ($existingUser) {
                // Ajout DIRECT de l'erreur (sans passer par les validateurs)
                $form->get('email')->addError(new FormError('Cet email est déjà utilisé'));    
            }

            // Traitement des erreurs de validation
            if (!$form->isValid()) {
                $this->addFlash('error', 'Il y a des erreurs dans le formulaire');
                foreach ($form->getErrors(true, true) as $error) {
                    // Si l'erreur concerne l'email, on l'ajoute spécifiquement au champ
                    if ($error->getCause() instanceof ConstraintViolation && $error->getCause()->getPropertyPath() === 'email') {
                        $form->get('email')->addError(new FormError($error->getMessage()));
                    }
                }
            }

            // Si le formulaire est valide après traitement des erreurs
            if ($form->isValid()) {
                // Hash du mot de passe
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                
                $user->setIsAdmin(false);
                
                // Persistance de l'utilisateur
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Inscription réussie !');
                return $this->redirectToRoute('login');
            } else {
                // Debug final
                dump([
                    'form_errors' => iterator_to_array($form->getErrors(true, true)),
                    'email_errors' => iterator_to_array($form->get('email')->getErrors()),
                    'submitted_data' => $form->getData()
                ]);
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
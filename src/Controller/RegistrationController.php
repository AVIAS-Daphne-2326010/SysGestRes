<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use App\Entity\UserAccount;
use App\Entity\Client;
use App\Entity\Role;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ): Response {
        $user = new UserAccount();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Encodage du mot de passe
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                // Vérification des champs pour déterminer le rôle
                if ($form->get('organizationName')->getData() && $form->get('address')->getData()) {
                    // Rôle client
                    $clientRole = $em->getRepository(Role::class)->findOneBy(['name' => 'client']);
                    if (!$clientRole) {
                        throw new \Exception("Le rôle 'client' n'existe pas dans la base de données.");
                    }
                    $user->setRole($clientRole);

                    // Création du client
                    $client = new Client();
                    $client->setOrganizationName($form->get('organizationName')->getData())
                        ->setAddress($form->get('address')->getData())
                        ->setUserAccount($user);
                    $em->persist($client);
                } else {
                    // Rôle utilisateur standard
                    $userRole = $em->getRepository(Role::class)->findOneBy(['name' => 'user']);
                    if (!$userRole) {
                        throw new \Exception("Le rôle 'user' n'existe pas dans la base de données.");
                    }
                    $user->setRole($userRole);
                }

                $user->setCreatedAt(new \DateTime());
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $logger->error('Erreur inscription: ' . $e->getMessage());
                $this->addFlash('error', "Une erreur s'est produite lors de l'inscription.");
            }
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            /** @var UserAccount $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email address has been verified.');
        return $this->redirectToRoute('app_register');
    }

    public function updateUser(
        Request $request,
        EntityManagerInterface $em,
        UserAccount $user // L'utilisateur que tu modifies
    ): Response {
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des champs pour déterminer le rôle
            if ($form->get('organizationName')->getData() && $form->get('address')->getData()) {
                // Rôle client
                $clientRole = $em->getRepository(Role::class)->findOneBy(['name' => 'client']);
                if (!$clientRole) {
                    throw new \Exception("Le rôle 'client' n'existe pas dans la base de données.");
                }
                $user->setRole($clientRole);
    
                // Vérifier si l'utilisateur a déjà un client associé
                if (!$user->getClient()) {
                    // Si non, créer un nouveau client
                    $client = new Client();
                    $client->setOrganizationName($form->get('organizationName')->getData())
                        ->setAddress($form->get('address')->getData())
                        ->setUserAccount($user);
                    $em->persist($client);
                }
            } else {
                // Rôle utilisateur standard
                $userRole = $em->getRepository(Role::class)->findOneBy(['name' => 'user']);
                if (!$userRole) {
                    throw new \Exception("Le rôle 'user' n'existe pas dans la base de données.");
                }
                $user->setRole($userRole);
            }
    
            // Mettre à jour les autres informations de l'utilisateur
            $em->persist($user);
            $em->flush();
    
            $this->addFlash('success', 'Modification réussie !');
            return $this->redirectToRoute('app_profile');
        }
    
        return $this->render('user/update.html.twig', [
            'updateForm' => $form->createView(),
        ]);
    }
    
}

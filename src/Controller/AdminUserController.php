<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\UserAccount;
use App\Entity\Client;
use App\Entity\BookingHistory;
use App\Form\UserAccountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_users', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(UserAccount::class)->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new UserAccount();
        $user->setCreatedAt(new \DateTime());

        $form = $this->createForm(UserAccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Le mot de passe est obligatoire pour la création
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $role = $form->get('role')->getData();
            $user->setRole($role);

            // Persist et flush d'abord l'utilisateur pour obtenir un ID
            $em->persist($user);
            $em->flush();

            if ($role->getName() === 'ROLE_CLIENT') {
                $clientData = $form->get('client')->getData();
                if ($clientData) {
                    $clientData->setUserAccount($user);
                    $user->setClient($clientData);
                    $em->persist($clientData);
                    $em->flush(); // Second flush pour le client
                }
            }

            $this->logAdminAction($em, 'Création utilisateur');
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $em->getRepository(UserAccount::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $currentPassword = $user->getPassword();
        $currentClient = $user->getClient();

        $form = $this->createForm(UserAccountType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du mot de passe
            if ($newPassword = $form->get('password')->getData()) {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            } else {
                $user->setPassword($currentPassword);
            }

            $newRole = $form->get('role')->getData();
            $user->setRole($newRole);

            // Gestion du client
            if ($newRole->getName() === 'ROLE_CLIENT') {
                $clientData = $form->get('client')->getData();
                if ($clientData) {
                    if ($currentClient) {
                        // Mettre à jour le client existant
                        $currentClient->setOrganizationName($clientData->getOrganizationName());
                        $currentClient->setAddress($clientData->getAddress());
                    } else {
                        // Créer un nouveau client
                        $clientData->setUserAccount($user);
                        $user->setClient($clientData);
                        $em->persist($clientData);
                    }
                }
            } else {
                // Si le rôle n'est plus client, vérifier s'il y a des ressources associées
                if ($currentClient) {
                    $resources = $em->getRepository(Resource::class)->findBy(['client' => $currentClient]);
                    if (count($resources) > 0) {
                        $this->addFlash('error', 'Ce client a des ressources associées et ne peut pas être supprimé');
                        return $this->redirectToRoute('admin_user_edit', ['id' => $id]);
                    }
                    $em->remove($currentClient);
                    $user->setClient(null);
                }
            }

            $this->logAdminAction($em, 'Modification utilisateur');
            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(UserAccount::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($client = $user->getClient()) {
            $resources = $em->getRepository(Resource::class)->findBy(['client' => $client]);
            if (count($resources) > 0) {
                $this->addFlash('error', 'Impossible de supprimer : ce client a des ressources associées');
                return $this->redirectToRoute('admin_users');
            }
            $em->remove($client);
        }

        $this->logAdminAction($em, 'Suppression utilisateur');
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('admin_users');
    }

    private function logAdminAction(EntityManagerInterface $em, string $action): void
{
    /** @var UserAccount $admin */
    $admin = $this->getUser();
    $log = new BookingHistory();
    $log->setStatus($action)
        ->setChangedAt(new \DateTime())
        ->setChangedBy($admin->getUsername()) 
        ->setUserAccount($admin);
    $em->persist($log);
}
}
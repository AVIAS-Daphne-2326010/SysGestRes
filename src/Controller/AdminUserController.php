<?php

namespace App\Controller;

use App\Entity\Role;
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

        $form = $this->createForm(UserAccountType::class, $user, [
            'is_edit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            $role = $form->get('role')->getData();
            $user->setRole($role);

            if ($role->getName() === 'ROLE_CLIENT') {
                $organizationName = $form->get('organization_name')->getData();
                $address = $form->get('address')->getData();

                $client = new Client();
                $client->setUserAccount($user);
                $client->setOrganizationName($organizationName);
                $client->setAddress($address);
                $em->persist($client);
            }

            $em->persist($user);
            $em->flush();

            // Log admin action
            /** @var UserAccount $admin */
            $admin = $this->getUser();
            $log = new BookingHistory();
            $log->setStatus('Création utilisateur')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($admin->getUsername())
                ->setUserAccount($admin);
            $em->persist($log);
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

        $isClient = $user->getRole()?->getName() === 'ROLE_CLIENT';
        $form = $this->createForm(UserAccountType::class, $user, [
            'is_edit' => true,
            'is_client' => $isClient,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newRole = $form->get('role')->getData();
            $user->setRole($newRole);

            var_dump($user->getId());
            $em->persist($user);
            $em->flush();

            if ($newRole->getName() === 'ROLE_CLIENT') {
                $organizationName = $form->get('organization_name')->getData();
                $address = $form->get('address')->getData();

                $client = $user->getClient();
                if (!$client) {
                    $client = new Client();
                    $client->setUserAccount($user);
                }
                $client->setOrganizationName($organizationName);
                $client->setAddress($address);
                $em->persist($client);
            } else {
                // Supprimer les infos client si le rôle n'est plus "ROLE_CLIENT"
                if ($user->getClient()) {
                    $em->remove($user->getClient());
                }
            }

            $em->flush();

            // Log admin action
            /** @var UserAccount $admin */
            $admin = $this->getUser();
            $log = new BookingHistory();
            $log->setStatus('Modification utilisateur')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($admin->getUsername())
                ->setUserAccount($admin);
            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(UserAccount::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Log admin action
        /** @var UserAccount $admin */
        $admin = $this->getUser();
        $log = new BookingHistory();
        $log->setStatus('Suppression utilisateur')
            ->setChangedAt(new \DateTime())
            ->setChangedBy($admin->getUsername())
            ->setUserAccount($admin);
        $em->persist($log);

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('admin_users');
    }
}

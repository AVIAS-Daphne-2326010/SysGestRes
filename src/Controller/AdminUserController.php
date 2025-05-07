<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\UserAccount;
use App\Entity\Client; // Importation de l'entité Client
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

        $form = $this->createForm(UserAccountType::class, $user, [
            'is_edit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Vérifier si l'utilisateur devient un client
            if ($form->get('role')->getData()->getName() === 'ROLE_CLIENT') {
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

            // Ajouter un log pour la création de l'utilisateur
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

        $isClient = false;
        if ($user->getRole()->getName() === 'ROLE_CLIENT') {
            $isClient = true; // Indiquer que l'utilisateur est un client
        }

        $form = $this->createForm(UserAccountType::class, $user, [
            'is_edit' => true,
            'is_client' => $isClient,  // Passer l'option à l'formulaire
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleId = $form->get('role')->getData();
            $role = $em->getRepository(Role::class)->find($roleId); 
            $user->setRole($role);
            
            if ($role->getName() === 'ROLE_CLIENT') {
                // Associer les informations de client
                $organizationName = $form->get('organization_name')->getData();
                $address = $form->get('address')->getData();

                $client = new Client();
                $client->setUserAccount($user);
                $client->setOrganizationName($organizationName);
                $client->setAddress($address);

                $em->persist($client);
            }

            $em->flush();

            // Ajouter un log pour la modification de l'utilisateur
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

        // Ajouter un log pour la suppression de l'utilisateur
        /** @var UserAccount $admin */
        $admin = $this->getUser();
        $log = new BookingHistory();
        $log->setStatus('Suppression utilisateur')
            ->setChangedAt(new \DateTime())
            ->setChangedBy($admin->getUsername())
            ->setUserAccount($admin);
        $em->persist($log);
        $em->flush();

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('admin_users');
    }
}

<?php

namespace App\Controller;

use App\Entity\UserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users')]
class AdminUserController extends AbstractController
{
    // Afficher la liste des utilisateurs
    #[Route('/', name: 'admin_users', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer tous les utilisateurs de la base
        $users = $em->getRepository(UserAccount::class)->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    // Afficher les détails d'un utilisateur spécifique
    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(UserAccount::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    // Ajouter un nouvel utilisateur
    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response  // Ajouté Request ici
    {
        $user = new UserAccount();

        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'));
            $user->setFirstName($request->request->get('first_name'));
            $user->setLastName($request->request->get('last_name'));
            $user->setPassword($request->request->get('password'));
            // N'oublie pas de hacher le mot de passe avant de l'enregistrer
            // $passwordHasher = $this->get('security.password_encoder');
            // $user->setPassword($passwordHasher->encodePassword($user, $user->getPassword()));

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/new.html.twig');
    }

    // Modifier un utilisateur spécifique
    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response  // Ajouté Request ici
    {
        $user = $em->getRepository(UserAccount::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if ($request->isMethod('POST')) {
            $user->setEmail($request->request->get('email'));
            $user->setFirstName($request->request->get('first_name'));
            $user->setLastName($request->request->get('last_name'));
            // N'oublie pas de gérer le mot de passe s'il est modifié
            // $user->setPassword($newPassword);
            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié avec succès');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
        ]);
    }

    // Supprimer un utilisateur spécifique
    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(UserAccount::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès');
        return $this->redirectToRoute('admin_users');
    }
}

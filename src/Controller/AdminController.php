<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/create-admin', name: 'create_admin')]
    public function createAdminUser(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $user = new Utilisateur();
        $user->setEmail('admin@example.com');
        $user->setNom('Admin');
        $user->setPassword(
            $passwordHasher->hashPassword($user, 'adminpassword')
        );
        $user->setIsAdmin(true);
        
        $em->persist($user);
        $em->flush();

        return new Response('Administrateur créé avec succès !');
    }
}
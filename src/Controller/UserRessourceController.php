<?php 

namespace App\Controller;

use App\Entity\Ressource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class UserRessourceController extends AbstractController
{
    #[Route('/user/ressource', name: 'user_ressource_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $ressources = $entityManager->getRepository(Ressource::class)->findAll();

        return $this->render('user/ressource/index.html.twig', [
            'ressources' => $ressources,
        ]);
    }
}
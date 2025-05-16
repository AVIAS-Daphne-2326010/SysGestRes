<?php

namespace App\Controller;

use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $query = $entityManager->createQuery(
            'SELECT DISTINCT r.type FROM App\Entity\Resource r'
        );
        $resourceTypes = $query->getResult();

        return $this->render('home/index.html.twig', [
            'resourceTypes' => $resourceTypes,
        ]);
    }
}

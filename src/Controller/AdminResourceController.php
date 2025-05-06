<?php

namespace App\Controller;

use App\Entity\Resource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/resources')]
class AdminResourceController extends AbstractController
{
    #[Route('/', name: 'admin_resources', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer toutes les ressources de la base
        $resources = $em->getRepository(Resource::class)->findAll();

        return $this->render('admin/resources/index.html.twig', [
            'resources' => $resources,
        ]);
    }
}

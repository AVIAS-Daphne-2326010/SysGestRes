<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\ResourceType;
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/new', name: 'admin_resource_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $resource = new Resource();
        
        // Récupérer un client existant (par exemple, le client avec l'ID 1)
        $client = $em->getRepository(Client::class)->find(1);
        
        if ($client) {
            $resource->setClient($client);  // Associer le client à la ressource
        } else {
            // Si aucun client n'est trouvé, tu peux gérer l'erreur ou en créer un nouveau
            // $client = new Client(); // Créer un nouveau client si nécessaire
            // $resource->setClient($client);
        }
        
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($resource);
            $em->flush();
    
            $this->addFlash('success', 'Ressource créée avec succès.');
    
            return $this->redirectToRoute('admin_resources');
        }
    
        return $this->render('admin/resources/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}


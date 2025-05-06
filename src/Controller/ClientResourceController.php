<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Resource;
use App\Entity\UserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client/resources')]
class ClientResourceController extends AbstractController
{
    #[Route('/', name: 'client_resources', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user instanceof UserAccount) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Charger le client associé à l'utilisateur
        $client = $em->getRepository(Client::class)->findOneBy(['userAccount' => $user]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable pour cet utilisateur.');
        }

        // Récupérer les ressources associées au client
        $resources = $em->getRepository(Resource::class)->findBy(['client' => $client]);

        // Affichage des ressources dans la vue
        return $this->render('client/resources/index.html.twig', [
            'resources' => $resources,
            'client' => $client,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Resource;
use App\Entity\UserAccount;
use App\Form\ResourceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/client/resources')]
class ClientResourceController extends AbstractController
{
    #[Route('/', name: 'client_resources', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        /** @var UserAccount $user */
        $user = $this->getUser();

        if (!$user instanceof UserAccount) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $client = $user->getClient();

        if (!$client) {
            throw $this->createNotFoundException('Aucun client associé à cet utilisateur.');
        }

        $resources = $em->getRepository(Resource::class)->findBy(['client' => $client]);

        return $this->render('client/resources/index.html.twig', [
            'resources' => $resources,
        ]);
    }

    #[Route('/new', name: 'client_resource_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        /** @var UserAccount $user */
        $user = $this->getUser();

        if (!$user instanceof UserAccount) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $client = $em->getRepository(Client::class)->findOneBy(['userAccount' => $user]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable pour cet utilisateur.');
        }

        $resource = new Resource();
        $resource->setClient($client);

        $form = $this->createForm(ResourceType::class, $resource);
        $form->remove('client');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customType = $form->get('type_custom')->getData();
            if ($customType) {
                $resource->setType($customType);
            }
            $em->persist($resource);
            $em->flush();

            $this->addFlash('success', 'La ressource a bien été créée.');

            return $this->redirectToRoute('client_resources');
        }

        return $this->render('client/resources/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'client_resource_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] Resource $resource,
        EntityManagerInterface $em
    ): Response {
        /** @var UserAccount $user */
        $user = $this->getUser();

        if (!$user instanceof UserAccount) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $client = $user->getClient();

        if (!$client || $resource->getClient()->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cette ressource.');
        }

        $form = $this->createForm(ResourceType::class, $resource);
        $form->remove('client');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customType = $form->get('type_custom')->getData();
            if ($customType) {
                $resource->setType($customType);
            }
            $em->flush();

            $this->addFlash('success', 'La ressource a bien été modifiée.');

            return $this->redirectToRoute('client_resources');
        }

        return $this->render('client/resources/edit.html.twig', [
            'resource' => $resource,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'client_resource_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        #[MapEntity] Resource $resource,
        EntityManagerInterface $em
    ): Response {
        /** @var UserAccount $user */
        $user = $this->getUser();

        if (!$user instanceof UserAccount) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $client = $user->getClient();

        if (!$client || $resource->getClient()->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer cette ressource.');
        }

        if ($this->isCsrfTokenValid('delete' . $resource->getId(), $request->request->get('_token'))) {
            $em->remove($resource);
            $em->flush();

            $this->addFlash('success', 'La ressource a bien été supprimée.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('client_resources');
    }
}

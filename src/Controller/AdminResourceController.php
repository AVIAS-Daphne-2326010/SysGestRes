<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\Client;
use App\Entity\BookingHistory;
use App\Entity\UserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\AdminTimeslotController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\ResourceType;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/resources')]
class AdminResourceController extends AbstractController
{
    #[Route('/', name: 'admin_resources', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $resources = $em->getRepository(Resource::class)->findAll();

        return $this->render('admin/resources/index.html.twig', [
            'resources' => $resources,
        ]);
    }

    #[Route('/new', name: 'admin_resource_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $resource = new Resource();

        $client = $em->getRepository(Client::class)->find(1);
        if ($client) {
            $resource->setClient($client);
        }

        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($resource);
            $em->flush();

            /** @var UserAccount $user */
            $user = $this->getUser();

            $log = new BookingHistory();
            $log->setStatus('Création de ressource')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setResource($resource);

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Ressource créée avec succès.');

            return $this->redirectToRoute('admin_resources');
        }

        return $this->render('admin/resources/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_resource_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Resource $resource, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ResourceType::class, $resource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            /** @var UserAccount $user */
            $user = $this->getUser();

            $log = new BookingHistory();
            $log->setStatus('Modification de ressource')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setResource($resource);

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Ressource modifiée avec succès.');

            return $this->redirectToRoute('admin_resources');
        }

        return $this->render('admin/resources/edit.html.twig', [
            'form' => $form->createView(),
            'resource' => $resource,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_resource_delete', methods: ['POST'])]
    public function delete(Request $request, Resource $resource, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $resource->getId(), $request->request->get('_token'))) {
            // Supprimer les logs liés à cette ressource
            $logs = $em->getRepository(BookingHistory::class)->findBy(['resource' => $resource]);
            foreach ($logs as $log) {
                $em->remove($log);
            }

            /** @var UserAccount $user */
            $user = $this->getUser();

            // Créer un log de suppression *sans* référence à la ressource (éviter la contrainte FK)
            $log = new BookingHistory();
            $log->setStatus('Suppression de ressource')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user);
                // ->setResource($resource); // Ne pas lier

            $em->persist($log);

            // Supprimer la ressource
            $em->remove($resource);

            $em->flush();

            $this->addFlash('success', 'Ressource supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_resources');
    }

    #[Route('/{id}', name: 'admin_resource_show', methods: ['GET'])]
    public function show(Resource $resource): Response
    {
        return $this->render('admin/resources/show.html.twig', [
            'resource' => $resource,
        ]);
    }
}

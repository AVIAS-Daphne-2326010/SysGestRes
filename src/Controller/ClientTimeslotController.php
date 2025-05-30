<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\Timeslot;
use App\Entity\BookingHistory;
use App\Entity\UserAccount;
use App\Entity\Client;
use App\Form\TimeslotType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/client/resource/{resource}/timeslots', requirements: ['resource' => '\d+'])]
class ClientTimeslotController extends AbstractController
{
    #[Route('/', name: 'client_timeslot_index', methods: ['GET'])]
    public function index(Resource $resource, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserAccount) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        $client = $em->getRepository(Client::class)->findOneBy(['userAccount' => $user]);
        if (!$client || $resource->getClient()->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Accès refusé à cette ressource.');
        }

        $timeslots = $em->getRepository(Timeslot::class)->findBy(['resource' => $resource]);

        return $this->render('client/timeslot/index.html.twig', [
            'resource' => $resource,
            'timeslots' => $timeslots,
        ]);
    }

    #[Route('/new', name: 'client_timeslot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, Resource $resource): Response
    {
        $user = $this->getUser();
        if (!$user instanceof UserAccount) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        $client = $em->getRepository(Client::class)->findOneBy(['userAccount' => $user]);
        if (!$client || $resource->getClient()->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Accès refusé à cette ressource.');
        }

        $timeslot = new Timeslot();
        $timeslot->setResource($resource);

        $form = $this->createForm(TimeslotType::class, $timeslot);
        $form->remove('resource');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($timeslot);
            $em->flush();

            $log = new BookingHistory();
            $log->setStatus('Création de créneau')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setResource($resource)
                ->setTimeslot($timeslot);

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Le créneau a bien été créé.');

            return $this->redirectToRoute('client_timeslot_index', ['resource' => $resource->getId()]);
        }

        return $this->render('client/timeslot/new.html.twig', [
            'resource' => $resource,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{timeslot_id}/edit', name: 'client_timeslot_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'timeslot_id')] Timeslot $timeslot,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof UserAccount) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }

        $resource = $timeslot->getResource();
        $client = $em->getRepository(Client::class)->findOneBy(['userAccount' => $user]);
        if (!$client || $resource->getClient()->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Accès refusé à ce créneau.');
        }

        $form = $this->createForm(TimeslotType::class, $timeslot);
        $form->remove('resource');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $log = new BookingHistory();
            $log->setStatus('Modification de créneau')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setResource($resource)
                ->setTimeslot($timeslot);

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Le créneau a bien été modifié.');

            return $this->redirectToRoute('client_timeslot_index', ['resource' => $resource->getId()]);
        }

        return $this->render('client/timeslot/edit.html.twig', [
            'resource' => $resource,
            'form' => $form->createView(),
            'timeslot' => $timeslot,
        ]);
    }

    #[Route('/{timeslot_id}/delete', name: 'client_timeslot_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Resource $resource,
        #[MapEntity(id: 'timeslot_id')] Timeslot $timeslot,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $timeslot->getId(), $request->request->get('_token'))) {
            /** @var UserAccount $user */
            $user = $this->getUser();

            $log = new BookingHistory();
            $log->setStatus('Suppression de créneau')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setResource($resource);
            
            $em->persist($log);
            
            $logs = $em->getRepository(BookingHistory::class)->findBy(['timeslot' => $timeslot]);
            foreach ($logs as $log) {
                $em->remove($log);
            }
            
            $em->flush(); 

            $em->remove($timeslot);
            $em->flush();

            $this->addFlash('success', 'Le créneau a bien été supprimé.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('client_timeslot_index', ['resource' => $resource->getId()]);
    }

}

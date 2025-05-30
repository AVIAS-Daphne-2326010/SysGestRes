<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Entity\Timeslot;
use App\Entity\BookingHistory;
use App\Entity\UserAccount;
use App\Form\TimeslotType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/admin/resource/{resource}/timeslots', requirements: ['resource' => '\d+'])]  
class AdminTimeslotController extends AbstractController
{
    #[Route('/', name: 'admin_timeslot_index', methods: ['GET'])]
    public function index(Resource $resource, EntityManagerInterface $em): Response
    {
        $timeslots = $em->getRepository(Timeslot::class)->findBy(['resource' => $resource]);

        return $this->render('admin/timeslot/index.html.twig', [
            'resource' => $resource,
            'timeslots' => $timeslots,
        ]);
    }

    #[Route('/new', name: 'admin_timeslot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, Resource $resource): Response
    {
        $timeslot = new Timeslot();
        $timeslot->setResource($resource);

        $form = $this->createForm(TimeslotType::class, $timeslot);
        $form->remove('resource');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($timeslot);
            $em->flush();

            // Log creation
            /** @var UserAccount $user */
            $user = $this->getUser();

            $log = new BookingHistory();
            $log->setStatus('Création de créneau')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setResource($resource)
                ->setTimeslot($timeslot); // Associe le créneau au log

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Le créneau a bien été créé.');

            return $this->redirectToRoute('admin_timeslot_index', ['resource' => $resource->getId()]);
        }

        return $this->render('admin/timeslot/new.html.twig', [
            'resource' => $resource,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{timeslot_id}/edit', name: 'admin_timeslot_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'timeslot_id')] Timeslot $timeslot,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(TimeslotType::class, $timeslot);
        $form->remove('resource');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            // Log modification
            /** @var UserAccount $user */
            $user = $this->getUser();

            $log = new BookingHistory();
            $log->setStatus('Modification de créneau')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setResource($timeslot->getResource()) // Associe la ressource au log
                ->setTimeslot($timeslot); // Associe le créneau au log

            $em->persist($log);
            $em->flush();

            $this->addFlash('success', 'Le créneau a bien été modifié.');

            return $this->redirectToRoute('admin_timeslot_index', ['resource' => $timeslot->getResource()->getId()]);
        }

        return $this->render('admin/timeslot/edit.html.twig', [
            'resource' => $timeslot->getResource(),
            'form' => $form->createView(),
            'timeslot' => $timeslot,
        ]);
    }

    #[Route('/{timeslot_id}/delete', name: 'admin_timeslot_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Resource $resource,
        #[MapEntity(id: 'timeslot_id')] Timeslot $timeslot,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $timeslot->getId(), $request->request->get('_token'))) {
            // Optionnel : supprimer les logs liés au timeslot avant suppression
            $logs = $em->getRepository(BookingHistory::class)->findBy(['timeslot' => $timeslot]);
            foreach ($logs as $log) {
                $em->remove($log);
            }

            /** @var UserAccount $user */
            $user = $this->getUser();

            // Log suppression sans associer le timeslot (pour éviter FK)
            $log = new BookingHistory();
            $log->setStatus('Suppression de créneau')
                ->setChangedAt(new \DateTime())
                ->setChangedBy($user->getUsername())
                ->setUserAccount($user)
                ->setResource($resource);
                // Ne pas faire $log->setTimeslot($timeslot);

            $em->persist($log);

            // Supprimer le timeslot
            $em->remove($timeslot);

            $em->flush();

            $this->addFlash('success', 'Le créneau a bien été supprimé.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_timeslot_index', ['resource' => $resource->getId()]);
    }

}

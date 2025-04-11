<?php

namespace App\Controller;

use App\Entity\Creneau;
use App\Form\CreneauType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CreneauRepository;

class CreneauController extends AbstractController
{
    #[Route('/creneau/create', name: 'creneau_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); 

        $creneau = new Creneau();
        $form = $this->createForm(CreneauType::class, $creneau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($creneau);
            $entityManager->flush();

            return $this->redirectToRoute('creneau_index');
        }

        return $this->render('creneau/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/creneau', name: 'creneau_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $creneaux = $em->getRepository(Creneau::class)->findAll();

        return $this->render('creneau/index.html.twig', [
            'creneaux' => $creneaux,
            'isAdmin' => in_array('ROLE_ADMIN', $user->getRoles()),
        ]);
    }

    #[Route('/creneau/edit/{id}', name: 'creneau_edit')]
    public function edit(Request $request, Creneau $creneau, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(CreneauType::class, $creneau);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Le créneau a été modifié avec succès.');

            return $this->redirectToRoute('creneau_index');
        }

        return $this->render('creneau/edit.html.twig', [
            'form' => $form->createView(),
            'creneau' => $creneau,
        ]);
    }

    #[Route('/creneau/delete/{id}', name: 'creneau_delete')]
    public function delete(Creneau $creneau, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($creneau);
        $em->flush();
        $this->addFlash('success', 'Le créneau a été supprimé.');

        return $this->redirectToRoute('creneau_index');
    }
}

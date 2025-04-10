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
    #[Route('admin/creneau/create', name: 'admin_creneau_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $creneau = new Creneau();

        $form = $this->createForm(CreneauType::class, $creneau);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($creneau);
            $entityManager->flush();

            return $this->redirectToRoute('admin_creneaux');  
        }

        return $this->render('admin/creneau/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/creneau', name: 'admin_creneaux')]
    public function index(EntityManagerInterface $em): Response
    {
        $creneaux = $em->getRepository(Creneau::class)->findAll();
        
        return $this->render('admin/creneau/index.html.twig', [
            'creneaux' => $creneaux,
        ]);
    }

    #[Route('/admin/edit/{id}', name: 'admin_creneau_edit')]
    public function edit(Request $request, Creneau $creneau, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CreneauType::class, $creneau);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Le créneau a été modifié avec succès.');

            return $this->redirectToRoute('admin_creneaux');
        }

        return $this->render('admin/creneau/edit.html.twig', [
            'form' => $form->createView(),
            'creneau' => $creneau,
        ]);
    }

    #[Route('/admin/delete/{id}', name: 'admin_creneau_delete')]
    public function delete(Creneau $creneau, EntityManagerInterface $em): Response
    {
        $em->remove($creneau);
        $em->flush();

        $this->addFlash('success', 'Le créneau a été supprimé.');

        return $this->redirectToRoute('admin_creneaux');
    }
}

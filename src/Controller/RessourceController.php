<?php

namespace App\Controller;

use App\Entity\Ressource;
use App\Form\RessourceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RessourceController extends AbstractController
{
    #[Route('/admin/ressource', name: 'admin_ressource')]
    public function index(EntityManagerInterface $em): Response
    {
        $ressources = $em->getRepository(Ressource::class)->findAll();
        return $this->render('admin/ressource/index.html.twig', [
            'ressources' => $ressources,
        ]);
    }

    #[Route('/admin/ressource/new', name: 'admin_ressource_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $ressource = new Ressource();
        $form = $this->createForm(RessourceType::class, $ressource);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ressource);
            $em->flush();
        
            $this->addFlash('success', 'Ressource ajoutée avec succès !');
            return $this->redirectToRoute('admin_ressource');
        }
    
        return $this->render('admin/ressource/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/ressource/edit/{id}', name: 'admin_ressource_edit')]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $ressource = $em->getRepository(Ressource::class)->find($id);

        if (!$ressource) {
            throw $this->createNotFoundException('Ressource non trouvée');
        }

        $form = $this->createForm(RessourceType::class, $ressource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Ressource modifiée avec succès !');
            return $this->redirectToRoute('admin_ressource');
        }

        return $this->render('admin/ressource/edit.html.twig', [
            'form' => $form->createView(),
            'ressource' => $ressource,
        ]);
    }

    #[Route('/admin/ressource/delete/{id}', name: 'admin_ressource_delete')]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $ressource = $em->getRepository(Ressource::class)->find($id);

        if (!$ressource) {
            throw $this->createNotFoundException('Ressource non trouvée');
        }

        $em->remove($ressource);
        $em->flush();

        $this->addFlash('success', 'Ressource supprimée avec succès !');
        return $this->redirectToRoute('admin_ressource');
    }
}

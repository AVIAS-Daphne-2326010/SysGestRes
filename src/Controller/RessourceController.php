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
    #[Route('/admin/ressources', name: 'admin_ressources')]
    public function index(EntityManagerInterface $em): Response
    {
        $ressources = $em->getRepository(Ressource::class)->findAll();
        return $this->render('admin/ressources/index.html.twig', [
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
            return $this->redirectToRoute('admin_ressources');
        }

        return $this->render('admin/ressources/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

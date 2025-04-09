<?php

namespace App\Controller;

use App\Entity\Creneau;
use App\Form\CreneauType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreneauController extends AbstractController
{
    #[Route('/creneau/create', name: 'create_creneau')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $creneau = new Creneau();

        $form = $this->createForm(CreneauType::class, $creneau);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($creneau);
            $entityManager->flush();

            return $this->redirectToRoute('accueil');  
        }

        return $this->render('creneau/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

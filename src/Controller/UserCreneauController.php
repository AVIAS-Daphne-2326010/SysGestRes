<?php 

namespace App\Controller;

use App\Entity\Creneau;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/user')]
class UserCreneauController extends AbstractController
{
    #[Route('/creneau', name: 'user_creneau_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $creneaux = $entityManager->getRepository(Creneau::class)->findAll();

        return $this->render('user/creneau/index.html.twig', [
            'creneaux' => $creneaux,
        ]);
    }
}
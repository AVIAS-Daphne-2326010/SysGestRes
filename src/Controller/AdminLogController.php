<?php

namespace App\Controller;

use App\Entity\BookingHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/logs')]
class AdminLogController extends AbstractController
{
    // Afficher la liste des logs
    #[Route('/', name: 'admin_logs', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer tous les logs de l'historique des réservations
        $logs = $em->getRepository(BookingHistory::class)->findAll();

        return $this->render('admin/logs/index.html.twig', [
            'logs' => $logs,
        ]);
    }

    // Afficher les détails d'un log spécifique
    #[Route('/{id}', name: 'admin_log_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $log = $em->getRepository(BookingHistory::class)->find($id);

        if (!$log) {
            throw $this->createNotFoundException('Log non trouvé');
        }

        return $this->render('admin/logs/show.html.twig', [
            'log' => $log,
        ]);
    }

    // Supprimer un log spécifique (si nécessaire)
    #[Route('/{id}/delete', name: 'admin_log_delete', methods: ['POST'])]
    public function delete(int $id, EntityManagerInterface $em): Response
    {
        $log = $em->getRepository(BookingHistory::class)->find($id);

        if (!$log) {
            throw $this->createNotFoundException('Log non trouvé');
        }

        // Supprimer le log
        $em->remove($log);
        $em->flush();

        $this->addFlash('success', 'Log supprimé avec succès');
        return $this->redirectToRoute('admin_logs');
    }
}

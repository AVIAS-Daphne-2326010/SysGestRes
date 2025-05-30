<?php

namespace App\Controller;

use App\Entity\BookingHistory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/logs')]
class AdminLogController extends AbstractController
{
    #[Route('/', name: 'admin_logs', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator): Response
    {
        $qb = $em->getRepository(BookingHistory::class)->createQueryBuilder('l');

        $changedBy = $request->query->get('changedBy');
        $changedAt = $request->query->get('changedAt');

        if ($changedBy) {
            $qb->andWhere('l.changedBy LIKE :changedBy')
               ->setParameter('changedBy', '%' . $changedBy . '%');
        }

        if ($changedAt) {
            $date = \DateTime::createFromFormat('Y-m-d', $changedAt);
            if ($date) {
                $startOfDay = (clone $date)->setTime(0, 0, 0);
                $endOfDay = (clone $date)->setTime(23, 59, 59);
                $qb->andWhere('l.changedAt BETWEEN :start AND :end')
                   ->setParameter('start', $startOfDay)
                   ->setParameter('end', $endOfDay);
            }
        }
        $qb->orderBy('l.changedAt', 'DESC');

        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            20,
            [
                'template' => 'pagination.html.twig',
            ]
        );

        return $this->render('admin/logs/index.html.twig', [
            'pagination' => $pagination,
            'changedBy' => $changedBy,
            'changedAt' => $changedAt,
        ]);
    }

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

    #[Route('/{id}/delete', name: 'admin_log_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_log_' . $id, $request->request->get('_token'))) {
            $log = $em->getRepository(BookingHistory::class)->find($id);

            if (!$log) {
                throw $this->createNotFoundException('Log non trouvé');
            }

            $em->remove($log);
            $em->flush();

            $this->addFlash('success', 'Log supprimé avec succès');
        }

        return $this->redirectToRoute('admin_logs');
    }
}

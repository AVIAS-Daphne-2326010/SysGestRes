<?php

namespace App\Controller;

use App\Entity\BookingHistory;
use App\Form\BookingHistoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/booking-history')]
class BookingHistoryController extends AbstractController
{
    #[Route('/', name: 'booking_history_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $bookingHistories = $entityManager->getRepository(BookingHistory::class)->findAll();

        return $this->render('booking_history/index.html.twig', [
            'booking_histories' => $bookingHistories,
        ]);
    }

    #[Route('/new', name: 'booking_history_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bookingHistory = new BookingHistory();
        $bookingHistory->setChangedAt(new \DateTimeImmutable());

        $form = $this->createForm(BookingHistoryType::class, $bookingHistory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bookingHistory);
            $entityManager->flush();

            return $this->redirectToRoute('booking_history_index');
        }

        return $this->render('booking_history/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'booking_history_show', methods: ['GET'])]
    public function show(BookingHistory $bookingHistory): Response
    {
        return $this->render('booking_history/show.html.twig', [
            'booking_history' => $bookingHistory,
        ]);
    }

    #[Route('/{id}/edit', name: 'booking_history_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BookingHistory $bookingHistory, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookingHistoryType::class, $bookingHistory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('booking_history_index');
        }

        return $this->render('booking_history/edit.html.twig', [
            'form' => $form->createView(),
            'booking_history' => $bookingHistory,
        ]);
    }

    #[Route('/{id}', name: 'booking_history_delete', methods: ['POST'])]
    public function delete(Request $request, BookingHistory $bookingHistory, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bookingHistory->getId(), $request->request->get('_token'))) {
            $entityManager->remove($bookingHistory);
            $entityManager->flush();
        }

        return $this->redirectToRoute('booking_history_index');
    }
}

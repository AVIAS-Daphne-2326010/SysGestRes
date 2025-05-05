<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/client/calendar')]
class ClientCalendarController extends AbstractController
{
    #[Route('/', name: 'client_calendar', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('client/calendar/index.html.twig');
    }
}

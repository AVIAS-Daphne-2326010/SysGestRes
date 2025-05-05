<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/profile', name: 'user_profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig');
    }

    #[Route('/user/resources', name: 'user_resources')]
    public function resources(): Response
    {
        return $this->render('user/resources.html.twig');
    }

    #[Route('/user/calendar', name: 'user_calendar')]
    public function calendar(): Response
    {
        return $this->render('user/calendar.html.twig');
    }

    #[Route('/user/bookings', name: 'user_bookings')]
    public function bookings(): Response
    {
        return $this->render('user/bookings.html.twig');
    }
}

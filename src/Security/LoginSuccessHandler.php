<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private RouterInterface $router) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $roleName = $user->getRole()->getName();

        return match ($roleName) {
            'ROLE_ADMIN' => new RedirectResponse($this->router->generate('admin_dashboard')),
            'ROLE_CLIENT' => new RedirectResponse($this->router->generate('client_dashboard')),
            default => new RedirectResponse($this->router->generate('user_dashboard')),
        };
    }
}

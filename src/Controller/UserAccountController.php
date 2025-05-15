<?php

namespace App\Controller;

use App\Entity\UserAccount;
use App\Form\UserAccountType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user_account')]
class UserAccountController extends AbstractController
{
    #[Route('/', name: 'user_account_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $userAccounts = $entityManager->getRepository(UserAccount::class)->findAll();

        return $this->render('user_account/index.html.twig', [
            'user_accounts' => $userAccounts,
        ]);
    }

    #[Route('/new', name: 'user_account_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userAccount = new UserAccount();
        $form = $this->createForm(UserAccountType::class, $userAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userAccount->setCreatedAt(new \DateTime());

            // Lier le client au userAccount si besoin
            if ($client = $userAccount->getClient()) {
                $client->setUserAccount($userAccount);
            }

            $entityManager->persist($userAccount);
            $entityManager->flush();

            return $this->redirectToRoute('user_account_index');
        }

        return $this->render('user_account/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'user_account_show', methods: ['GET'])]
    public function show(UserAccount $userAccount): Response
    {
        return $this->render('user_account/show.html.twig', [
            'user_account' => $userAccount,
        ]);
    }

    #[Route('/{id}/edit', name: 'user_account_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserAccount $userAccount, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserAccountType::class, $userAccount);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user_account_index');
        }

        return $this->render('user_account/edit.html.twig', [
            'form' => $form->createView(),
            'user_account' => $userAccount,
        ]);
    }

    #[Route('/{id}', name: 'user_account_delete', methods: ['POST'])]
    public function delete(Request $request, UserAccount $userAccount, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $userAccount->getId(), $request->request->get('_token'))) {
            $entityManager->remove($userAccount);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_account_index');
    }
}

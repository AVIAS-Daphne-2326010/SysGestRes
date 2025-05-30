<?php

namespace App\Controller;

use App\Entity\UserAccount;
use App\Form\UserAccountType;
use App\Entity\Resource;
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
        $form = $this->createForm(UserAccountType::class, $userAccount, [
            'is_edit' => true,
        ]);
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

    #[Route('/ressources/{type}', name: 'user_resources_by_type', methods: ['GET'])]
    public function showByType(string $type, EntityManagerInterface $entityManager, Request $request): Response
    {
        $q = trim($request->query->get('q', ''));
        $location = trim($request->query->get('location', ''));
        $capacity = $request->query->get('capacity');

        $qb = $entityManager->createQueryBuilder()
            ->select('r', 'c')
            ->from(Resource::class, 'r')
            ->leftJoin('r.client', 'c')
            ->where('r.type = :type')
            ->setParameter('type', $type);

        if ($q !== '') {
            $qb->andWhere('r.name LIKE :q OR r.description LIKE :q')
            ->setParameter('q', '%' . $q . '%');
        }

        if ($location !== '') {
            $qb->andWhere('r.location LIKE :location')
            ->setParameter('location', '%' . $location . '%');
        }

        if (is_numeric($capacity) && (int)$capacity > 0) {
            $qb->andWhere('r.capacity >= :capacity')
            ->setParameter('capacity', (int)$capacity);
        }

        $resources = $qb->getQuery()->getResult();

        return $this->render('user/ressource/show.html.twig', [
            'resources' => $resources,
            'type' => $type,
            'q' => $q, 
            'location' => $location,
            'capacity' => $capacity,
        ]);
    }

}

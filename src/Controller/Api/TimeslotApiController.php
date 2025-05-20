<?php

namespace App\Controller\Api;

use App\Entity\Timeslot;
use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api/timeslots')]
class TimeslotApiController extends AbstractController
{
    #[Route('', name: 'api_timeslots_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $resourceId = $request->query->get('resourceId');

        $client = null;
        if ($user) {
            $client = $entityManager->getRepository(Client::class)->findOneBy([
                'userAccount' => $user
            ]);
        }

        if (!$client && !$resourceId) {
            return $this->json(['error' => 'Client non trouvé et aucun resourceId fourni'], 404);
        }

        $qb = $entityManager->getRepository(Timeslot::class)->createQueryBuilder('t')
            ->innerJoin('t.resource', 'r');

        if ($resourceId) {
            $qb->andWhere('r.id = :resourceId')
               ->setParameter('resourceId', $resourceId);
        } elseif ($client) {
            $resources = $client->getResources();
            $resourceIds = [];

            foreach ($resources as $resource) {
                $resourceIds[] = $resource->getId();
            }

            if (empty($resourceIds)) {
                return $this->json([]);
            }

            $qb->andWhere('r.id IN (:resourceIds)')
               ->setParameter('resourceIds', $resourceIds);
        } else {
            return $this->json(['error' => 'Aucune ressource accessible'], 403);
        }

        $timeslots = $qb->getQuery()->getResult();

        $data = [];

        foreach ($timeslots as $timeslot) {
            $data[] = [
                'id' => $timeslot->getId(),
                'title' => $timeslot->isAvailable() ? 'Disponible' : 'Réservé',
                'start' => $timeslot->getStartDatetime()->format('Y-m-d\TH:i:s'),
                'end' => $timeslot->getEndDatetime()->format('Y-m-d\TH:i:s'),
                'color' => $timeslot->isAvailable() ? '#28a745' : '#dc3545',
                'extendedProps' => [
                    'resource' => $timeslot->getResource()->getName(),
                    'isAvailable' => $timeslot->isAvailable()
                ]
            ];
        }

        return $this->json($data);
    }
}

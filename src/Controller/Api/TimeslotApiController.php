<?php

namespace App\Controller\Api;

use App\Entity\Timeslot;
use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/timeslots')]
class TimeslotApiController extends AbstractController
{
    #[Route('', name: 'api_timeslots_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        $client = $entityManager->getRepository(Client::class)->findOneBy([
            'userAccount' => $user
        ]);

        if (!$client) {
            return $this->json(['error' => 'Client non trouvé'], 404);
        }

        $resources = $client->getResources(); 
        $resourceIds = [];

        foreach ($resources as $resource) {
            $resourceIds[] = $resource->getId();
        }

        if (empty($resourceIds)) {
            return $this->json([]);
        }

        $timeslots = $entityManager->getRepository(Timeslot::class)->createQueryBuilder('t')
            ->where('t.isAvailable = true')
            ->andWhere('t.resource IN (:resources)')
            ->setParameter('resources', $resourceIds)
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($timeslots as $timeslot) {
            $data[] = [
                'id' => $timeslot->getId(),
                'title' => 'Créneau disponible',
                'start' => $timeslot->getStartDatetime()->format('Y-m-d\TH:i:s'),
                'end' => $timeslot->getEndDatetime()->format('Y-m-d\TH:i:s'),
                'color' => 'green'
            ];
        }

        return $this->json($data);
    }
}

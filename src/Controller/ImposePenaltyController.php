<?php

declare(strict_types=1);

namespace App\Controller;
use App\Entity\Driver;
use App\Entity\Penalty;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ImposePenaltyController extends AbstractController
{
    public function imposePenalty(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $driver = $entityManager->getRepository(Driver::class)->find($request->request->get('driverLicenseNumber'));
        if (!$driver) {
            return $this->json(['success' => false, 'errors' => ['driver not found!']]);
        }
        $sumOfPoints = 0;
        foreach ($driver->penalties as $penalty) {
            $sumOfPoints += $penalty->payedAt === null || $penalty->occurredAt->diff(new \DateTimeImmutable())->y < 2
                ? $penalty->numberOfPoints : 0;
        }
        $newPenalty = new Penalty();
        $newPenalty->driver = $driver;
        $newPenalty->series = $request->request->get('series');
        $newPenalty->number = $request->request->get('number');
        $newPenalty->occurredAt = new \DateTimeImmutable();
        $newPenalty->numberOfPoints = $request->request->get('numberOfPoints');
        $newPenalty->payedAt = $request->request->get('isPaidOnSpot') === 1 ? new \DateTimeImmutable() : null;
        $entityManager->persist($newPenalty);
        $entityManager->flush();
        if ($sumOfPoints + $newPenalty->numberOfPoints > $this->limit($driver)) {
            return $this->json(['success' => false, 'errors' => ['penalty imposed, but driver']]);
        }
        return $this->json(['success' => true, 'errors' => []]);
    }

    private function limit(Driver $driver): int
    {
        if ($driver->examPassedAt->diff(new \DateTimeImmutable())->y >= 2) {
            return 24;
        }

        return 20;
    }
}

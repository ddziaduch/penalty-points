<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Penalty
{
    public string $series;
    public int $number;
    public int $numberOfPoints;
    public \DateTimeImmutable $occurredAt;
    public ?\DateTimeImmutable $payedAt;
    #[ORM\ManyToOne(targetEntity: Driver::class, inversedBy: 'penalties')]
    #[ORM\JoinColumn(name: 'driver_id', referencedColumnName: 'id')]
    public Driver $driver;
}

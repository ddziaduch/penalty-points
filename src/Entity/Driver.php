<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class Driver
{
    #[ORM\Id()]
    public string $licenseNumber;
    public \DateTimeImmutable $examPassedAt;
    #[ORM\OneToMany(targetEntity: Penalty::class, mappedBy: 'driver')]
    /** @var Collection<Penalty> */
    public Collection $penalties;

    public function __construct() {
        $this->penalties = new ArrayCollection();
    }
}

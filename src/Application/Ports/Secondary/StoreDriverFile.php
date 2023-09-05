<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Secondary;

use ddziaduch\PenaltyPoints\Domain\DriverFile;

interface StoreDriverFile
{
    public function store(DriverFile $driverFile): void;
}

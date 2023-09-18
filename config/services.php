<?php

declare(strict_types=1);

use ddziaduch\PenaltyPoints\Adapters\Primary\Cli\PoliceOfficerImposePenaltyCliAdapter;
use ddziaduch\PenaltyPoints\Adapters\Primary\Http\PoliceOfficerImposePenaltyHttpAdapter;
use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Adapters\Secondary\SystemClock;
use ddziaduch\PenaltyPoints\Application\ImposePenaltyService;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->defaults()->autowire(false)->autoconfigure(false);

    $services->set(ClockInterface::class, SystemClock::class);

    $services->set(InMemoryDriverFiles::class)->args([
        service(ClockInterface::class),
    ]);

    $services->alias(StoreDriverFile::class, InMemoryDriverFiles::class);
    $services->alias(GetDriverFile::class, InMemoryDriverFiles::class);

    $services->set(ImposePenalty::class, ImposePenaltyService::class)->args([
        service(ClockInterface::class),
        service(GetDriverFile::class),
        service(StoreDriverFile::class),
    ]);

    $services->set(PoliceOfficerImposePenaltyHttpAdapter::class)->args([
        service(ImposePenalty::class),
    ])->tag('controller.service_arguments');

    $services->set(PoliceOfficerImposePenaltyCliAdapter::class)->args([
        service(ImposePenalty::class),
    ])->tag('console.command');
};

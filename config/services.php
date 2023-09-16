<?php

declare(strict_types=1);

use ddziaduch\PenaltyPoints\Adapters\Primary\PoliceOfficerImposeUnpaidPenaltyCliAdapter;
use ddziaduch\PenaltyPoints\Adapters\Primary\PoliceOfficerImposePenaltyHttpAdapter;
use ddziaduch\PenaltyPoints\Adapters\Secondary\SystemClock;
use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\PoliceOfficerService;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->defaults()->autowire(false)->autoconfigure(false);

    $services->set(ClockInterface::class, SystemClock::class);
    $services->set(InMemoryDriverFiles::class);

    $services->set(PoliceOfficer::class, PoliceOfficerService::class)->args([
        service(ClockInterface::class),
        service(InMemoryDriverFiles::class),
        service('event_dispatcher'),
        service(InMemoryDriverFiles::class),
    ]);

    $services->set(PoliceOfficerImposePenaltyHttpAdapter::class)->args([
        service(PoliceOfficer::class),
    ])->tag('controller.service_arguments');

    $services->set(PoliceOfficerImposeUnpaidPenaltyCliAdapter::class)->args([
        service(PoliceOfficer::class),
    ])->tag('console.command');
};

<?php

declare(strict_types=1);

use ddziaduch\PenaltyPoints\Adapters\Primary\ImposePenaltyHttpAdapter;
use ddziaduch\PenaltyPoints\Adapters\Secondary\Clock;
use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\ImposePenaltyService;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->defaults()->autowire(false)->autoconfigure(false);

    $services->set(ClockInterface::class, Clock::class);
    $services->set(InMemoryDriverFiles::class);

    $services->set(ImposePenalty::class, ImposePenaltyService::class)->args([
        service(ClockInterface::class),
        service(InMemoryDriverFiles::class),
        service('event_dispatcher'),
        service(InMemoryDriverFiles::class),
    ]);

    $services->set(ImposePenaltyHttpAdapter::class)->args([
        service(ImposePenalty::class),
    ])->tag('controller.service_arguments');
};

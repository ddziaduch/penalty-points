<?php

declare(strict_types=1);

use ddziaduch\PenaltyPoints\Adapters\Primary\PoliceOfficerHttpAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $configurator): void {
    $configurator->add(
        PoliceOfficerHttpAdapter::class,
        'impose-penalty/driver/{driverLicenseNumber}/points/{numberOfPoints}',
    )->requirements([
        'driverLicenseNumber' => '\d+/\d+/\d+',
        'numberOfPoints' => '\d+'
    ])->methods([Request::METHOD_POST])->controller(PoliceOfficerHttpAdapter::class);
};

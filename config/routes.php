<?php

declare(strict_types=1);

use ddziaduch\PenaltyPoints\Adapters\Primary\Http\ImposePenaltyHttpAdapter;
use ddziaduch\PenaltyPoints\Adapters\Primary\Http\PayPenaltyHttpAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $configurator): void {
    $configurator->add(
        name: 'police-officer:impose-unpaid-penalty',
        path: 'drivers/{driverLicenseNumber}/penalties/unpaid/series/{penaltySeries}/number/{penaltyNumber}/points/{numberOfPoints}',
    )->defaults([
        'isPaidOnSpot' => false,
    ])->methods([Request::METHOD_POST])->controller(ImposePenaltyHttpAdapter::class);

    $configurator->add(
        name: 'police-officer:impose-penalty-paid-on-spot',
        path: 'drivers/{driverLicenseNumber}/penalties/paid-on-spot/series/{penaltySeries}/number/{penaltyNumber}/points/{numberOfPoints}',
    )->defaults([
        'isPaidOnSpot' => true,
    ])->methods([Request::METHOD_POST])->controller(ImposePenaltyHttpAdapter::class);

    $configurator->add(
        name: 'driver:pay-penalty',
        path: 'drivers/{driverLicenseNumber}/penalties/series/{penaltySeries}/number/{penaltyNumber}/pay',
    )->methods([Request::METHOD_POST])->controller(PayPenaltyHttpAdapter::class);
};

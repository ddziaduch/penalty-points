<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application;

final class DriverFileDoesNotExist extends \OutOfBoundsException
{
    public function __construct(string $licenseNumber)
    {
        parent::__construct(
            sprintf(
                'Driver file with license number %s does not exist',
                $licenseNumber,
            ),
        );
    }
}

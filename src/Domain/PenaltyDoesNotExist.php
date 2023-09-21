<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class PenaltyDoesNotExist extends \OutOfBoundsException
{
    public function __construct(string $series, int $number)
    {
        parent::__construct(
            sprintf(
                'Penalty series %s / number %s does not exist',
                $series,
                $number,
            ),
        );
    }
}

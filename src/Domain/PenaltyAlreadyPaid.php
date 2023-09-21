<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class PenaltyAlreadyPaid extends \DomainException
{
    public function __construct(string $series, int $number)
    {
        parent::__construct(
            sprintf(
                'Penalty series %s, number %s is already paid',
                $series,
                $number,
            ),
        );
    }
}

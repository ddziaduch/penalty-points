<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Secondary;

use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use Psr\Clock\ClockInterface;

final class InMemoryDriverFiles implements GetDriverFile, StoreDriverFile
{
    /** @var array<string, DriverFile> */
    private array $driverFiles;

    public function __construct(ClockInterface $clock)
    {
        $newbie = self::newbieDriverFile($clock->now());
        $pirate = self::pirateDriverFile($clock->now());

        $this->driverFiles = [
            $newbie->licenseNumber => $newbie,
            $pirate->licenseNumber => $pirate,
        ];
    }

    public function get(string $drivingLicenceNumber): DriverFile
    {
        if (!array_key_exists($drivingLicenceNumber, $this->driverFiles)) {
            throw new \OutOfBoundsException('The driver file does not exist');
        }

        return $this->driverFiles[$drivingLicenceNumber];
    }

    public function store(DriverFile $driverFile): void
    {
        $this->driverFiles[$driverFile->licenseNumber] = $driverFile;
    }

    public static function newbieDriverFile(\DateTimeImmutable $now): DriverFile
    {
        $newbie = new DriverFile(
            licenseNumber: 'newbie',
            examPassedAt: $now->modify('-8 months'),
        );
        $newbie->imposePenalty(
            series: 'AA',
            number: 1,
            occurredAt: $newbie->examPassedAt->modify('+2 months'),
            numberOfPoints: 10,
            isPaidOnSpot: true,
        );
        $newbie->imposePenalty(
            series: 'AB',
            number: 997,
            occurredAt: $newbie->examPassedAt->modify('+4 months'),
            numberOfPoints: 5,
            isPaidOnSpot: true,
        );

        return $newbie;
    }

    public static function pirateDriverFile(\DateTimeImmutable $now): DriverFile
    {
        $pirate = new DriverFile(
            licenseNumber: 'pirate',
            examPassedAt: $now->modify('-2 years'),
        );
        $pirate->imposePenalty(
            series: 'XY',
            number: 99,
            occurredAt: $now->modify('-15 months'),
            numberOfPoints: 15,
            isPaidOnSpot: true,
        );
        $pirate->imposePenalty(
            series: 'YX',
            number: 987,
            occurredAt: $now->modify('-10 days'),
            numberOfPoints: 15,
            isPaidOnSpot: true,
        );
        return $pirate;
    }
}

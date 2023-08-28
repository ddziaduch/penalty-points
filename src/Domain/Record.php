<?php

declare(strict_types=1);

namespace ddziaduch\PenalityPoints\Domain;

final class Record
{
	/** @var Penalities[] */
	private array $penalities;

	public function __construct(
		private DateTimeImmutable $createdAt,
		Penality ...$penalities,
	) {
		$this->penalities = $penalities;
	}

	public function addPenality(Penality $penality): void
	{
		$this->penalities[] = $penality;
	}

	public function isLimitExceeded(DateTimeImmutable $now): bool
	{

	}

	public function maxLimit(DateTimeImmutable $now): int
	{
		if ($now->diff($this->createdAt)->y >= 1) {
			return 24;
		}

		return 20;
	}
}
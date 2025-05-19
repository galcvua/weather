<?php

declare(strict_types=1);

namespace App\Domain\Weather\ValueObject;

final readonly class Condition
{
    public function __construct(
        public string $label,
        public ?string $iconUrl = null,
    ) {
    }
}

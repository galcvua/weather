<?php

declare(strict_types=1);

namespace App\Domain\Weather\Exception;

use RuntimeException;

final class WeatherProviderException extends RuntimeException
{
    public static function failedToFetch(string $provider, ?string $reason = null): self
    {
        $message = sprintf('Failed to fetch weather data from provider "%s".', $provider);

        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        return new self($message);
    }
}

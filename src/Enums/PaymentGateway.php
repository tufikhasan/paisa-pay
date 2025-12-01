<?php

namespace TufikHasan\PaisaPay\Enums;

enum PaymentGateway: string
{
    case STRIPE = 'stripe';

    /**
     * Get all gateway values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all gateway names as comma-separated string.
     */
    public static function valuesString(): string
    {
        return implode(',', self::values());
    }

    /**
     * Check if a value is valid gateway.
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    /**
     * Get gateway from string value.
     */
    public static function fromString(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Get display name for the gateway.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::STRIPE => 'Stripe',
        };
    }

    /**
     * Check if gateway is enabled in config.
     */
    public static function isEnabled(string $value): bool
    {
        return config("paisapay.gateways.{$value}.enabled", false);
    }
}

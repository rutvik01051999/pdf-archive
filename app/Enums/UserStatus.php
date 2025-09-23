<?php

namespace App\Enums;

enum UserStatus: string
{
    case INACTIVE = 'inactive';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            self::SUSPENDED => 'warning',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function name(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function value(): string
    {
        return match ($this) {
            self::INACTIVE => 'inactive',
            self::ACTIVE => 'active',
            self::SUSPENDED => 'suspended',
        };
    }

    public static function values(): array
    {
        return [
            self::ACTIVE->value(),
            self::INACTIVE->value(),
            self::SUSPENDED->value(),
        ];
    }

    public static function labels(): array
    {
        return [
            self::ACTIVE->label(),
            self::INACTIVE->label(),
            self::SUSPENDED->label(),
        ];
    }

    public function badgeHtml(): string
    {
        return "<span class=\"badge bg-outline-{$this->color()}\">{$this->label()}</span>";
    }

    public static function randomStatus(): self
    {
        return match (rand(0, 2)) {
            0 => self::INACTIVE,
            1 => self::ACTIVE,
            2 => self::SUSPENDED,
        };
    }

    public static function options(): array
    {
        return [
            self::ACTIVE->value() => self::ACTIVE->label(),
            self::INACTIVE->value() => self::INACTIVE->label(),
            self::SUSPENDED->value() => self::SUSPENDED->label(),
        ];
    }

    public static function fromValue(string|int $value): self
    {
        return match ($value) {
            'active', '1' => self::ACTIVE,
            'inactive', '0' => self::INACTIVE,
            'suspended', '2' => self::SUSPENDED,
            'pending', '3' => self::SUSPENDED, // Map pending to suspended
            default => self::INACTIVE, // Default fallback
        };
    }

}

<?php

namespace App\Casts;

use App\Enums\UserStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class UserStatusCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): UserStatus
    {
        // Handle both string and integer values
        return UserStatus::fromValue($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof UserStatus) {
            return $value->value;
        }

        // If it's already a string, return it
        if (is_string($value)) {
            return $value;
        }

        // If it's an integer, convert it to string
        if (is_int($value)) {
            return (string) $value;
        }

        // Default fallback
        return 'inactive';
    }
}
<?php

namespace App\Models;

use RuntimeException;

class WorkoutSetData
{
    public function __construct(
        public readonly int $id,
        public readonly int $setOrder,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            id: self::requireInt($payload, 'id'),
            setOrder: self::requireInt($payload, 'set_order'),
        );
    }

    private static function requireInt(array $payload, string $key): int
    {
        if (! array_key_exists($key, $payload) || ! is_numeric($payload[$key])) {
            throw new RuntimeException("Missing or invalid integer field [{$key}] in workout set payload.");
        }

        return (int) $payload[$key];
    }
}

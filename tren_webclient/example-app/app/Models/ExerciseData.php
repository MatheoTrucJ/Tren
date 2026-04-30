<?php

namespace App\Models;

use RuntimeException;

class ExerciseData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly bool $isPersonal,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self(
            id: self::requireInt($payload, 'id'),
            name: self::requireString($payload, 'name'),
            description: self::requireString($payload, 'description'),
            isPersonal: self::requireBool($payload, 'is_personal'),
        );
    }

    /**
     * @return array<int, ExerciseData>
     */
    public static function collectionFromPayload(mixed $payload): array
    {
        if (! is_array($payload) || ! array_is_list($payload)) {
            throw new RuntimeException('Expected exercise API response to be a list.');
        }

        return array_map(
            static function (mixed $exercisePayload): ExerciseData {
                if (! is_array($exercisePayload)) {
                    throw new RuntimeException('Invalid exercise payload item.');
                }

                return self::fromArray($exercisePayload);
            },
            $payload
        );
    }

    private static function requireInt(array $payload, string $key): int
    {
        if (! array_key_exists($key, $payload) || ! is_numeric($payload[$key])) {
            throw new RuntimeException("Missing or invalid integer field [{$key}] in exercise payload.");
        }

        return (int) $payload[$key];
    }

    private static function requireString(array $payload, string $key): string
    {
        if (! array_key_exists($key, $payload) || ! is_string($payload[$key])) {
            throw new RuntimeException("Missing or invalid string field [{$key}] in exercise payload.");
        }

        return $payload[$key];
    }

    private static function requireBool(array $payload, string $key): bool
    {
        if (! array_key_exists($key, $payload) || ! is_bool($payload[$key])) {
            throw new RuntimeException("Missing or invalid boolean field [{$key}] in exercise payload.");
        }

        return $payload[$key];
    }
}

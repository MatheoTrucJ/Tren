<?php

namespace App\Models;

use RuntimeException;

class WorkoutData
{
    /**
     * @param  array<int, WorkoutExerciseData>  $exercises
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
        public readonly int $userId,
        public readonly array $exercises,
    ) {}

    public static function fromArray(array $payload): self
    {
        $exercisesPayload = self::requireList($payload, 'exercises');

        return new self(
            id: self::requireInt($payload, 'id'),
            name: self::requireString($payload, 'name'),
            description: self::requireString($payload, 'description'),
            userId: self::requireInt($payload, 'user_id'),
            exercises: array_map(
                static function (mixed $exercisePayload): WorkoutExerciseData {
                    if (! is_array($exercisePayload)) {
                        throw new RuntimeException('Invalid workout exercise payload item.');
                    }

                    return WorkoutExerciseData::fromArray($exercisePayload);
                },
                $exercisesPayload
            ),
        );
    }

    /**
     * @return array<int, WorkoutData>
     */
    public static function collectionFromPayload(mixed $payload): array
    {
        if (! is_array($payload) || ! array_is_list($payload)) {
            throw new RuntimeException('Expected workout API response to be a list.');
        }

        return array_map(
            static function (mixed $workoutPayload): WorkoutData {
                if (! is_array($workoutPayload)) {
                    throw new RuntimeException('Invalid workout payload item.');
                }

                return self::fromArray($workoutPayload);
            },
            $payload
        );
    }

    private static function requireList(array $payload, string $key): array
    {
        if (! array_key_exists($key, $payload) || ! is_array($payload[$key]) || ! array_is_list($payload[$key])) {
            throw new RuntimeException("Missing or invalid list field [{$key}] in workout payload.");
        }

        return $payload[$key];
    }

    private static function requireInt(array $payload, string $key): int
    {
        if (! array_key_exists($key, $payload) || ! is_numeric($payload[$key])) {
            throw new RuntimeException("Missing or invalid integer field [{$key}] in workout payload.");
        }

        return (int) $payload[$key];
    }

    private static function requireString(array $payload, string $key): string
    {
        if (! array_key_exists($key, $payload) || ! is_string($payload[$key])) {
            throw new RuntimeException("Missing or invalid string field [{$key}] in workout payload.");
        }

        return $payload[$key];
    }
}

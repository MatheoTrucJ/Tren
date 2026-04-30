<?php

namespace App\Models;

use RuntimeException;

class WorkoutExerciseData
{
    /**
     * @param  array<int, WorkoutSetData>  $sets
     */
    public function __construct(
        public readonly ExerciseData $exercise,
        public readonly int $orderIndex,
        public readonly array $sets,
    ) {}

    public static function fromArray(array $payload): self
    {
        $exercisePayload = self::requireArray($payload, 'exercise');
        $setsPayload = self::requireList($payload, 'sets');

        return new self(
            exercise: ExerciseData::fromArray($exercisePayload),
            orderIndex: self::requireInt($payload, 'order_index'),
            sets: array_map(
                static function (mixed $setPayload): WorkoutSetData {
                    if (! is_array($setPayload)) {
                        throw new RuntimeException('Invalid workout set payload item.');
                    }

                    return WorkoutSetData::fromArray($setPayload);
                },
                $setsPayload
            ),
        );
    }

    private static function requireArray(array $payload, string $key): array
    {
        if (! array_key_exists($key, $payload) || ! is_array($payload[$key])) {
            throw new RuntimeException("Missing or invalid object field [{$key}] in workout exercise payload.");
        }

        return $payload[$key];
    }

    private static function requireList(array $payload, string $key): array
    {
        if (! array_key_exists($key, $payload) || ! is_array($payload[$key]) || ! array_is_list($payload[$key])) {
            throw new RuntimeException("Missing or invalid list field [{$key}] in workout exercise payload.");
        }

        return $payload[$key];
    }

    private static function requireInt(array $payload, string $key): int
    {
        if (! array_key_exists($key, $payload) || ! is_numeric($payload[$key])) {
            throw new RuntimeException("Missing or invalid integer field [{$key}] in workout exercise payload.");
        }

        return (int) $payload[$key];
    }
}

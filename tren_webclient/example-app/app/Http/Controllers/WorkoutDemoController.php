<?php

namespace App\Http\Controllers;

use App\Helpers\ConnectionHelper;
use App\Http\Requests\StoreWorkoutDemoRequest;
use App\Models\ExerciseData;
use App\Models\WorkoutData;
use App\Models\WorkoutExerciseData;
use App\Models\WorkoutSetData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class WorkoutDemoController extends Controller
{
    private const DEFAULT_USER_ID = 1;

    private const DEFAULT_SET_COUNT = 3;

    public function index(Request $request, ConnectionHelper $connectionHelper): View
    {
        $userId = $this->resolveUserId($request->query('user', self::DEFAULT_USER_ID));
        $pageData = $this->loadPageData($userId, $connectionHelper);
        $exerciseRows = $this->resolveExerciseRows($request, $pageData['exercises']);

        return view('workouts.index', [
            'userId' => $userId,
            'workouts' => $pageData['workouts'],
            'exercises' => $pageData['exercises'],
            'exerciseRows' => $exerciseRows,
            'nextExerciseIndex' => count($exerciseRows),
            'errorMessage' => $pageData['errorMessage'],
            'successMessage' => $request->session()->get('status'),
            'flashErrorMessage' => $request->session()->get('errorMessage'),
            'defaultWorkoutName' => 'Demo workout',
            'defaultWorkoutDescription' => 'Created from the Laravel demo page.',
            'defaultSetCount' => self::DEFAULT_SET_COUNT,
        ]);
    }

    public function store(StoreWorkoutDemoRequest $request, ConnectionHelper $connectionHelper): RedirectResponse
    {
        $validated = $request->validated();
        $userId = (int) $validated['user'];

        try {
            $exerciseCatalog = $this->indexExercisesById($this->fetchExercises($userId, $connectionHelper));
            $payload = $this->buildWorkoutPayload($validated, $exerciseCatalog);

            $response = $connectionHelper->post('/workouts', $payload);
            $response->throw();
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            return redirect()
                ->route('workouts.index', ['user' => $userId])
                ->withInput()
                ->with('errorMessage', $this->formatErrorMessage($exception));
        }

        return redirect()
            ->route('workouts.index', ['user' => $userId])
            ->with('status', 'Workout saved successfully.');
    }

    /**
     * @return array{workouts: array<int, array<string, mixed>>, exercises: array<int, array<string, mixed>>, errorMessage: ?string}
     */
    private function loadPageData(int $userId, ConnectionHelper $connectionHelper): array
    {
        $workouts = [];
        $exercises = [];
        $errorMessages = [];

        try {
            $workouts = $this->fetchWorkouts($userId, $connectionHelper);
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            $errorMessages[] = $this->formatErrorMessage($exception);
        }

        try {
            $exercises = $this->formatExercises($this->fetchExercises($userId, $connectionHelper));
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            $errorMessages[] = $this->formatErrorMessage($exception);
        }

        return [
            'workouts' => $workouts,
            'exercises' => $exercises,
            'errorMessage' => $errorMessages === [] ? null : implode(' ', $errorMessages),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $availableExercises
     * @return array<int, array<string, mixed>>
     */
    private function resolveExerciseRows(Request $request, array $availableExercises): array
    {
        $oldExercises = $request->old('exercises');

        if (! is_array($oldExercises) || $oldExercises === []) {
            return [[
                'exercise_id' => $availableExercises[0]['id'] ?? null,
                'order_index' => 1,
                'set_count' => self::DEFAULT_SET_COUNT,
            ]];
        }

        $exerciseRows = [];

        foreach (array_values($oldExercises) as $exerciseRow) {
            if (! is_array($exerciseRow)) {
                continue;
            }

            $exerciseRows[] = [
                'exercise_id' => $exerciseRow['exercise_id'] ?? ($availableExercises[0]['id'] ?? null),
                'order_index' => $exerciseRow['order_index'] ?? 1,
                'set_count' => $exerciseRow['set_count'] ?? self::DEFAULT_SET_COUNT,
            ];
        }

        return $exerciseRows === [] ? [[
            'exercise_id' => $availableExercises[0]['id'] ?? null,
            'order_index' => 1,
            'set_count' => self::DEFAULT_SET_COUNT,
        ]] : $exerciseRows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchWorkouts(int $userId, ConnectionHelper $connectionHelper): array
    {
        $response = $connectionHelper->get("/workouts/user/{$userId}");
        $response->throw();

        $workouts = WorkoutData::collectionFromPayload($response->json());

        return array_map(
            static fn (WorkoutData $workout): array => [
                'id' => $workout->id,
                'name' => $workout->name,
                'description' => $workout->description,
                'exercise_count' => count($workout->exercises),
                'exercises' => array_map(
                    static fn (WorkoutExerciseData $exercise): array => [
                        'order_index' => $exercise->orderIndex,
                        'exercise' => [
                            'id' => $exercise->exercise->id,
                            'name' => $exercise->exercise->name,
                            'description' => $exercise->exercise->description,
                            'is_personal' => $exercise->exercise->isPersonal,
                        ],
                        'sets' => array_map(
                            static fn (WorkoutSetData $set): array => [
                                'id' => $set->id,
                                'set_order' => $set->setOrder,
                            ],
                            $exercise->sets
                        ),
                    ],
                    $workout->exercises
                ),
            ],
            $workouts
        );
    }

    /**
     * @return array<int, ExerciseData>
     */
    private function fetchExercises(int $userId, ConnectionHelper $connectionHelper): array
    {
        $response = $connectionHelper->get("/workouts/exercises/user/{$userId}");
        $response->throw();

        return ExerciseData::collectionFromPayload($response->json());
    }

    /**
     * @param  array<int, ExerciseData>  $exercises
     * @return array<int, array<string, mixed>>
     */
    private function formatExercises(array $exercises): array
    {
        return array_map(
            static fn (ExerciseData $exercise): array => [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'description' => $exercise->description,
                'is_personal' => $exercise->isPersonal,
            ],
            $exercises
        );
    }

    /**
     * @param  array<int, ExerciseData>  $exercises
     * @return array<int, ExerciseData>
     */
    private function indexExercisesById(array $exercises): array
    {
        $indexedExercises = [];

        foreach ($exercises as $exercise) {
            $indexedExercises[$exercise->id] = $exercise;
        }

        return $indexedExercises;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, ExerciseData>  $exerciseCatalog
     * @return array<string, mixed>
     */
    private function buildWorkoutPayload(array $validated, array $exerciseCatalog): array
    {
        $exercises = [];

        foreach ($validated['exercises'] as $exerciseRow) {
            $exerciseId = (int) $exerciseRow['exercise_id'];

            if (! array_key_exists($exerciseId, $exerciseCatalog)) {
                throw new RuntimeException("Exercise {$exerciseId} is not available for the selected user.");
            }

            $exercise = $exerciseCatalog[$exerciseId];
            $sets = [];
            $setCount = (int) $exerciseRow['set_count'];

            for ($setOrder = 1; $setOrder <= $setCount; $setOrder++) {
                $sets[] = [
                    'id' => 0,
                    'set_order' => $setOrder,
                ];
            }

            $exercises[] = [
                'exercise' => [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'description' => $exercise->description,
                    'is_personal' => $exercise->isPersonal,
                ],
                'order_index' => (int) $exerciseRow['order_index'],
                'sets' => $sets,
            ];
        }

        return [
            'id' => 0,
            'name' => trim((string) $validated['workout_name']),
            'description' => trim((string) ($validated['workout_description'] ?? '')),
            'user_id' => (int) $validated['user'],
            'exercises' => $exercises,
        ];
    }

    private function resolveUserId(mixed $userId): int
    {
        $resolvedUserId = (int) $userId;

        abort_if($resolvedUserId < 1, 422, 'The "user" query parameter must be a positive integer.');

        return $resolvedUserId;
    }

    private function formatErrorMessage(ConnectionException|RequestException|RuntimeException $exception): string
    {
        if ($exception instanceof ConnectionException) {
            return 'Could not connect to the workout API. Make sure tren_api is running.';
        }

        if ($exception instanceof RequestException) {
            $status = $exception->response?->status();

            return "Workout API request failed with status {$status}.";
        }

        return $exception->getMessage();
    }
}

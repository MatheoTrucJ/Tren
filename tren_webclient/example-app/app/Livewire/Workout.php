<?php

namespace App\Livewire;

use App\Helpers\ConnectionHelper;
use App\Models\WorkoutData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Livewire\Component;
use RuntimeException;

class Workout extends Component
{
    public array $workouts = [];

    public ?string $errorMessage = null;

    private const USER_ID = 1;

    public function mount(ConnectionHelper $connectionHelper): void
    {
        try {
            $response = $connectionHelper->get('/workouts/user/'.self::USER_ID);
            $response->throw();
            $workouts = WorkoutData::collectionFromPayload($response->json());
            $this->workouts = array_map(
                static function (WorkoutData $workout): array {
                    return [
                        'id' => $workout->id,
                        'name' => $workout->name,
                        'description' => $workout->description,
                        'exercise_count' => count($workout->exercises),
                    ];
                },
                $workouts
            );
        } catch (ConnectionException) {
            $this->errorMessage = 'Could not connect to the workout API. Make sure tren_api is running.';
            $this->workouts = [];
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $this->errorMessage = "Workout API request failed with status {$status}.";
            $this->workouts = [];
        } catch (RuntimeException $exception) {
            $this->errorMessage = $exception->getMessage();
            $this->workouts = [];
        }
    }

    public function render(): View
    {
        return view('livewire.livewire-test', [
            'userId' => self::USER_ID,
        ]);
    }
}

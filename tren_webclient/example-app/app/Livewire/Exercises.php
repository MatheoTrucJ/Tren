<?php

namespace App\Livewire;

use App\Helpers\ConnectionHelper;
use App\Models\ExerciseData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Livewire\Component;
use RuntimeException;

class Exercises extends Component
{
    public array $exercises = [];

    public ?string $errorMessage = null;

    private const USER_ID = 1;

    public function mount(ConnectionHelper $connectionHelper): void
    {
        try {
            $response = $connectionHelper->get('/workouts/exercises/user/'.self::USER_ID);
            $response->throw();
            $exercises = ExerciseData::collectionFromPayload($response->json());
            $this->exercises = array_map(
                static function (ExerciseData $exercise): array {
                    return [
                        'id' => $exercise->id,
                        'name' => $exercise->name,
                        'description' => $exercise->description,
                        'is_personal' => $exercise->isPersonal,
                    ];
                },
                $exercises
            );
        } catch (ConnectionException) {
            $this->errorMessage = 'Could not connect to the workout API. Make sure tren_api is running.';
            $this->exercises = [];
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $this->errorMessage = "Workout API request failed with status {$status}.";
            $this->exercises = [];
        } catch (RuntimeException $exception) {
            $this->errorMessage = $exception->getMessage();
            $this->exercises = [];
        }
    }

    public function render(): View
    {
        return view('livewire.exercises-poc', [
            'userId' => self::USER_ID,
        ]);
    }
}

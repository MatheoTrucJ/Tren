<?php

namespace App\Http\Controllers;

use App\Helpers\ConnectionHelper;
use App\Models\WorkoutData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use RuntimeException;

class WorkoutPocController extends Controller
{
    public function __invoke(Request $request, ConnectionHelper $connectionHelper): View
    {
        $userId = $request->integer('user', 1);
        abort_if($userId < 1, 422, 'The "user" query parameter must be a positive integer.');

        $workouts = [];
        $errorMessage = null;

        try {
            $response = $connectionHelper->get("/workouts/user/{$userId}");
            $response->throw();
            $workouts = WorkoutData::collectionFromPayload($response->json());
        } catch (ConnectionException) {
            $errorMessage = 'Could not connect to the workout API. Make sure tren_api is running.';
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $errorMessage = "Workout API request failed with status {$status}.";
        } catch (RuntimeException $exception) {
            $errorMessage = $exception->getMessage();
        }

        return view(
            'workouts.index',
            [
                'userId' => $userId,
                'workouts' => $workouts,
                'errorMessage' => $errorMessage,
            ]
        );
    }
}

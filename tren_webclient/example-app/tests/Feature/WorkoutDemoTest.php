<?php

use Illuminate\Support\Facades\Http;

test('the workout demo page shows workouts and exercises', function () {
    config(['services.tren_api.url' => 'http://tren.test']);

    Http::fake([
        'http://tren.test/workouts/user/4' => Http::response([
            [
                'id' => 10,
                'name' => 'Upper body strength',
                'description' => 'Bench and row focus',
                'user_id' => 4,
                'exercises' => [
                    [
                        'order_index' => 1,
                        'exercise' => [
                            'id' => 21,
                            'name' => 'Bench Press',
                            'description' => 'Chest press',
                            'is_personal' => false,
                        ],
                        'sets' => [
                            ['id' => 1, 'set_order' => 1],
                            ['id' => 2, 'set_order' => 2],
                        ],
                    ],
                ],
            ],
        ], 200),
        'http://tren.test/workouts/exercises/user/4' => Http::response([
            ['id' => 21, 'name' => 'Bench Press', 'description' => 'Chest press', 'is_personal' => false],
            ['id' => 22, 'name' => 'Barbell Row', 'description' => 'Back row', 'is_personal' => false],
        ], 200),
    ]);

    $response = $this->get(route('workouts.index', ['user' => 4]));

    $response->assertOk();
    $response->assertSee('Workout demo');
    $response->assertSee('Add exercise');
    $response->assertSee('Upper body strength');
    $response->assertSee('Bench Press');
    $response->assertSee('Barbell Row');
    $response->assertSee('Set 1');
});

test('the workout demo page saves a workout and redirects back', function () {
    config(['services.tren_api.url' => 'http://tren.test']);

    Http::fake([
        'http://tren.test/workouts/exercises/user/4' => Http::response([
            ['id' => 21, 'name' => 'Bench Press', 'description' => 'Chest press', 'is_personal' => false],
        ], 200),
        'http://tren.test/workouts' => Http::response(['id' => 42], 201),
    ]);

    $response = $this->post(route('workouts.store'), [
        'user' => 4,
        'workout_name' => 'Demo workout',
        'workout_description' => 'Created from the test suite.',
        'exercises' => [
            [
                'exercise_id' => 21,
                'order_index' => 1,
                'set_count' => 3,
            ],
            [
                'exercise_id' => 22,
                'order_index' => 2,
                'set_count' => 4,
            ],
        ],
    ]);

    $response->assertRedirect(route('workouts.index', ['user' => 4]));
    $response->assertSessionHas('status', 'Workout saved successfully.');

    Http::assertSentCount(2);
    Http::assertSent(static function ($request): bool {
        return $request->method() === 'GET'
            && $request->url() === 'http://tren.test/workouts/exercises/user/4';
    });

    Http::assertSent(static function ($request): bool {
        $data = $request->data();

        return $request->method() === 'POST'
            && $request->url() === 'http://tren.test/workouts'
            && $data['name'] === 'Demo workout'
            && $data['description'] === 'Created from the test suite.'
            && $data['user_id'] === 4
            && count($data['exercises']) === 2
            && $data['exercises'][0]['exercise']['id'] === 21
            && count($data['exercises'][0]['sets']) === 3
            && $data['exercises'][1]['exercise']['id'] === 22
            && count($data['exercises'][1]['sets']) === 4;
    });
});

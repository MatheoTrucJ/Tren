<?php

use Illuminate\Support\Facades\Http;

test('returns a successful response', function () {
    config(['services.tren_api.url' => 'http://tren.test']);

    Http::fake([
        'http://tren.test/workouts/user/1' => Http::response([], 200),
        'http://tren.test/workouts/exercises/user/1' => Http::response([], 200),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Workout demo');
    $response->assertSee('Add exercise');
});

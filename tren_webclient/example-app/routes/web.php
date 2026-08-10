<?php

use App\Http\Controllers\WorkoutDemoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WorkoutDemoController::class, 'index'])->name('home');
Route::get('/workouts', [WorkoutDemoController::class, 'index'])->name('workouts.index');
Route::post('/workouts', [WorkoutDemoController::class, 'store'])->name('workouts.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

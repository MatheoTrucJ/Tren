<?php

use App\Livewire\Exercises;
use App\Livewire\Workout;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/exercises', Exercises::class);
Route::get('/workouts', Workout::class);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

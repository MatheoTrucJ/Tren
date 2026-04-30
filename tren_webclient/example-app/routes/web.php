<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\WorkoutList;
use Livewire\Volt\Volt;

Route::view('/', 'welcome')->name('home');
Route::get('/workouts', function () {
    return view('livewire-test');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

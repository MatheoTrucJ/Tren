<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Workout POC</title>
        <style>
            :root {
                color-scheme: light dark;
            }

            body {
                margin: 0;
                font-family: Inter, Arial, sans-serif;
                background: #0b1020;
                color: #e6e8f0;
            }

            .container {
                max-width: 960px;
                margin: 0 auto;
                padding: 2rem 1rem 3rem;
            }

            .header {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 1rem;
                align-items: center;
                margin-bottom: 1.5rem;
            }

            .title {
                margin: 0;
                font-size: 1.8rem;
            }

            .meta {
                margin: 0.5rem 0 0;
                color: #a8b0c8;
            }

            .filter {
                display: flex;
                gap: 0.5rem;
                align-items: center;
            }

            .filter input {
                border: 1px solid #34405f;
                background: #151d34;
                color: #e6e8f0;
                border-radius: 0.5rem;
                padding: 0.55rem 0.7rem;
                width: 6rem;
            }

            .filter button {
                border: 0;
                border-radius: 0.5rem;
                background: #4f7cff;
                color: #fff;
                font-weight: 600;
                padding: 0.55rem 0.9rem;
                cursor: pointer;
            }

            .grid {
                display: grid;
                gap: 1rem;
            }

            .card {
                border: 1px solid #2a3555;
                border-radius: 0.9rem;
                background: #121a2f;
                padding: 1rem;
            }

            .card h2 {
                margin: 0;
                font-size: 1.2rem;
            }

            .description {
                margin: 0.5rem 0 0.8rem;
                color: #b8c0d9;
            }

            .exercise {
                border-top: 1px solid #273153;
                margin-top: 0.8rem;
                padding-top: 0.8rem;
            }

            .exercise-name {
                margin: 0;
                font-size: 1rem;
            }

            .exercise-meta {
                margin: 0.25rem 0 0.5rem;
                color: #9daccc;
                font-size: 0.9rem;
            }

            .sets {
                display: flex;
                gap: 0.45rem;
                flex-wrap: wrap;
            }

            .set {
                border: 1px solid #3a4b79;
                border-radius: 999px;
                padding: 0.3rem 0.55rem;
                font-size: 0.82rem;
                color: #dbe4ff;
                background: #1a2544;
            }

            .empty {
                border: 1px dashed #3b4770;
                border-radius: 0.9rem;
                text-align: center;
                color: #b6c0dc;
                padding: 2rem 1rem;
            }

            .error {
                border: 1px solid #704040;
                border-radius: 0.9rem;
                background: #2d1217;
                color: #ffd6da;
                padding: 0.9rem 1rem;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body>
        <main class="container">
            <div class="header">
                <div>
                    <h1 class="title">Workout POC</h1>
                    <p class="meta">
                        Showing {{ count($workouts) }} workout{{ count($workouts) === 1 ? '' : 's' }} for user {{ $userId }}.
                    </p>
                </div>

                <form method="GET" class="filter">
                    <label for="user">User</label>
                    <input id="user" type="number" min="1" name="user" value="{{ $userId }}">
                    <button type="submit">Load</button>
                </form>
            </div>

            @if($errorMessage !== null)
                <section class="error">
                    {{ $errorMessage }}
                </section>
            @endif

            <div class="grid">
                @forelse($workouts as $workout)
                    <section class="card">
                        <h2>{{ $workout->name }}</h2>
                        <p class="description">{{ $workout->description !== '' ? $workout->description : 'No description.' }}</p>

                        @forelse($workout->exercises as $workoutExercise)
                            <article class="exercise">
                                <h3 class="exercise-name">{{ $workoutExercise->orderIndex }}. {{ $workoutExercise->exercise->name }}</h3>
                                <p class="exercise-meta">
                                    {{ $workoutExercise->exercise->description !== '' ? $workoutExercise->exercise->description : 'No exercise description.' }}
                                </p>

                                <div class="sets">
                                    @forelse($workoutExercise->sets as $set)
                                        <span class="set">Set {{ $set->setOrder }}</span>
                                    @empty
                                        <span class="set">No sets</span>
                                    @endforelse
                                </div>
                            </article>
                        @empty
                            <article class="exercise">
                                <p class="exercise-meta">No exercises in this workout.</p>
                            </article>
                        @endforelse
                    </section>
                @empty
                    <section class="empty">
                        No workouts returned for this user.
                    </section>
                @endforelse
            </div>
        </main>
    </body>
</html>

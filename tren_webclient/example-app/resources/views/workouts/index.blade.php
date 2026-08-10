<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Workout demo</title>
        <style>
            body {
                margin: 0;
                font-family: Arial, sans-serif;
                background: #f4f5f7;
                color: #111827;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 24px;
            }

            .card {
                background: #fff;
                border: 1px solid #d1d5db;
                border-radius: 16px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            }

            .row {
                display: flex;
                flex-wrap: wrap;
                gap: 16px;
                justify-content: space-between;
                align-items: flex-end;
            }

            .grid {
                display: grid;
                gap: 20px;
            }

            .grid-two {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }

            .field {
                display: grid;
                gap: 8px;
            }

            input,
            select,
            textarea,
            button {
                font: inherit;
            }

            input,
            select,
            textarea {
                width: 100%;
                box-sizing: border-box;
                border: 1px solid #cbd5e1;
                border-radius: 10px;
                padding: 10px 12px;
                background: #fff;
            }

            button {
                border: 0;
                border-radius: 10px;
                padding: 10px 14px;
                background: #4f46e5;
                color: #fff;
                font-weight: 700;
                cursor: pointer;
            }

            .button-secondary {
                background: #e5e7eb;
                color: #111827;
            }

            .muted {
                color: #6b7280;
            }

            .badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 999px;
                background: #eef2ff;
                color: #4338ca;
                font-size: 12px;
                font-weight: 700;
            }

            .badge-gray {
                background: #f3f4f6;
                color: #4b5563;
            }

            .alert {
                border-radius: 12px;
                padding: 12px 14px;
                margin-top: 16px;
            }

            .success {
                background: #ecfdf5;
                border: 1px solid #a7f3d0;
                color: #065f46;
            }

            .error {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #991b1b;
            }

            .warning {
                background: #fffbeb;
                border: 1px solid #fde68a;
                color: #92400e;
            }

            .workout,
            .exercise,
            .exercise-form {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 14px;
            }

            .workout + .workout,
            .exercise + .exercise,
            .exercise-form + .exercise-form {
                margin-top: 12px;
            }

            .sets {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 12px;
            }

            .set {
                border-radius: 999px;
                background: #e5e7eb;
                padding: 4px 10px;
                font-size: 12px;
                font-weight: 700;
            }

            ul {
                margin: 8px 0 0;
                padding-left: 20px;
            }

            .exercise-form-header {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 12px;
            }

            .exercise-form-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            }
        </style>
    </head>
    <body>
        <main class="container">
            <section class="card">
                <div class="row">
                    <div style="flex: 1 1 420px;">
                        <p class="muted" style="margin: 0 0 8px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">Workout demo</p>
                        <h1 style="margin: 0 0 8px; font-size: 30px;">Load a user, create a workout, save it, and review the result.</h1>
                        <p class="muted" style="margin: 0;">This page pulls workouts and exercises from the Rust API, then posts a new workout back to the same API for manual testing.</p>
                    </div>

                    <form method="GET" action="{{ route('workouts.index') }}" class="field" style="min-width: 220px;">
                        <label for="user" style="font-weight: 700;">User ID</label>
                        <input id="user" type="number" min="1" name="user" value="{{ $userId }}">
                        <button type="submit">Load user</button>
                    </form>
                </div>

                @if($successMessage !== null)
                    <div class="alert success">{{ $successMessage }}</div>
                @endif

                @if($flashErrorMessage !== null)
                    <div class="alert error">{{ $flashErrorMessage }}</div>
                @endif

                @if($errorMessage !== null)
                    <div class="alert error">{{ $errorMessage }}</div>
                @endif

                @if($errors->any())
                    <div class="alert warning">
                        <strong>Fix the following:</strong>
                        <ul>
                            @foreach($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            <div class="grid grid-two">
                <section class="card">
                    <h2 style="margin-top: 0;">Create workout</h2>
                    <p class="muted">Sends a nested workout payload to <code>/workouts</code>.</p>

                    <form method="POST" action="{{ route('workouts.store') }}" class="grid" style="gap: 14px;" id="workout-form">
                        @csrf
                        <input type="hidden" name="user" value="{{ $userId }}">

                        <div class="grid grid-two">
                            <label class="field">
                                <span>Workout name</span>
                                <input type="text" name="workout_name" value="{{ old('workout_name', $defaultWorkoutName) }}" maxlength="48">
                            </label>

                            <label class="field">
                                <span>Description</span>
                                <textarea name="workout_description" rows="4" maxlength="1000">{{ old('workout_description', $defaultWorkoutDescription) }}</textarea>
                            </label>
                        </div>

                        <section class="grid" style="gap: 12px;">
                            <div class="exercise-form-header">
                                <div>
                                    <h3 style="margin: 0;">Exercises</h3>
                                    <p class="muted" style="margin: 4px 0 0;">Add as many exercises as you want before saving.</p>
                                </div>

                                <button type="button" class="button-secondary" id="add-exercise">Add exercise</button>
                            </div>

                            <div id="exercise-list" class="grid" style="gap: 12px;">
                                @foreach($exerciseRows as $index => $exerciseRow)
                                    <article class="exercise-form" data-exercise-row>
                                        <div class="exercise-form-header">
                                            <strong>Exercise block</strong>
                                            <button type="button" class="button-secondary" data-remove-exercise>Remove</button>
                                        </div>

                                        <div class="exercise-form-grid">
                                            <label class="field">
                                                <span>Exercise</span>
                                                <select name="exercises[{{ $index }}][exercise_id]">
                                                    @forelse($exercises as $exercise)
                                                        <option value="{{ $exercise['id'] }}" @selected((string) $exerciseRow['exercise_id'] === (string) $exercise['id'])>
                                                            {{ $exercise['name'] }}
                                                        </option>
                                                    @empty
                                                        <option value="">No exercises available</option>
                                                    @endforelse
                                                </select>
                                            </label>

                                            <label class="field">
                                                <span>Exercise order</span>
                                                <input type="number" min="1" name="exercises[{{ $index }}][order_index]" value="{{ $exerciseRow['order_index'] }}">
                                            </label>

                                            <label class="field">
                                                <span>Set count</span>
                                                <input type="number" min="1" max="20" name="exercises[{{ $index }}][set_count]" value="{{ $exerciseRow['set_count'] }}">
                                            </label>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>

                        <button type="submit">Save workout</button>
                    </form>

                    <template id="exercise-row-template">
                        <article class="exercise-form" data-exercise-row>
                            <div class="exercise-form-header">
                                <strong>Exercise block</strong>
                                <button type="button" class="button-secondary" data-remove-exercise>Remove</button>
                            </div>

                            <div class="exercise-form-grid">
                                <label class="field">
                                    <span>Exercise</span>
                                    <select name="exercises[__INDEX__][exercise_id]">__EXERCISE_OPTIONS__</select>
                                </label>

                                <label class="field">
                                    <span>Exercise order</span>
                                    <input type="number" min="1" name="exercises[__INDEX__][order_index]" value="__ORDER_INDEX__">
                                </label>

                                <label class="field">
                                    <span>Set count</span>
                                    <input type="number" min="1" max="20" name="exercises[__INDEX__][set_count]" value="__SET_COUNT__">
                                </label>
                            </div>
                        </article>
                    </template>
                </section>

                <section class="card">
                    <h2 style="margin-top: 0;">Available exercises</h2>
                    <p class="muted">Retrieved from the API for user {{ $userId }}.</p>

                    @if($exercises === [])
                        <div class="exercise">No exercises were returned for this user.</div>
                    @else
                        @foreach($exercises as $exercise)
                            <article class="exercise">
                                <div class="row" style="align-items: flex-start;">
                                    <div style="flex: 1 1 260px;">
                                        <h3 style="margin: 0 0 6px;">{{ $exercise['name'] }}</h3>
                                        <p class="muted" style="margin: 0;">{{ $exercise['description'] !== '' ? $exercise['description'] : 'No description.' }}</p>
                                    </div>

                                    <span class="badge {{ $exercise['is_personal'] ? '' : 'badge-gray' }}">
                                        {{ $exercise['is_personal'] ? 'Personal' : 'General' }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    @endif
                </section>
            </div>

            <section class="card">
                <div class="row">
                    <div>
                        <h2 style="margin: 0 0 6px;">Workouts for user {{ $userId }}</h2>
                        <p class="muted" style="margin: 0;">Saved workouts reappear here after a successful submission.</p>
                    </div>

                    <span class="badge badge-gray">{{ count($workouts) }} workout{{ count($workouts) === 1 ? '' : 's' }}</span>
                </div>

                @if($workouts === [])
                    <div class="exercise" style="margin-top: 16px;">No workouts returned for this user yet.</div>
                @else
                    <div style="margin-top: 16px;">
                        @foreach($workouts as $workout)
                            <article class="workout">
                                <div class="row" style="align-items: flex-start;">
                                    <div style="flex: 1 1 320px;">
                                        <h3 style="margin: 0 0 6px;">{{ $workout['name'] }}</h3>
                                        <p class="muted" style="margin: 0;">{{ $workout['description'] !== '' ? $workout['description'] : 'No description.' }}</p>
                                    </div>

                                    <span class="badge">{{ $workout['exercise_count'] }} exercise{{ $workout['exercise_count'] === 1 ? '' : 's' }}</span>
                                </div>

                                <div style="margin-top: 14px;">
                                    @foreach($workout['exercises'] as $workoutExercise)
                                        <div class="exercise">
                                            <div class="row" style="align-items: flex-start;">
                                                <div style="flex: 1 1 320px;">
                                                    <strong>{{ $workoutExercise['order_index'] }}. {{ $workoutExercise['exercise']['name'] }}</strong>
                                                    <p class="muted" style="margin: 6px 0 0;">{{ $workoutExercise['exercise']['description'] !== '' ? $workoutExercise['exercise']['description'] : 'No exercise description.' }}</p>
                                                </div>
                                            </div>

                                            <div class="sets">
                                                @foreach($workoutExercise['sets'] as $set)
                                                    <span class="set">Set {{ $set['set_order'] }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const exerciseOptions = @json($exercises);
                const exerciseList = document.getElementById('exercise-list');
                const addExerciseButton = document.getElementById('add-exercise');
                const template = document.getElementById('exercise-row-template').innerHTML.trim();
                let nextExerciseIndex = Number(@json($nextExerciseIndex));
                const defaultSetCount = Number(@json($defaultSetCount));

                const escapeHtml = (value) => String(value)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');

                const buildExerciseOptions = (selectedExerciseId = '') => {
                    if (exerciseOptions.length === 0) {
                        return '<option value="">No exercises available</option>';
                    }

                    return exerciseOptions.map((exercise) => {
                        const selected = String(exercise.id) === String(selectedExerciseId) ? ' selected' : '';

                        return `<option value="${escapeHtml(exercise.id)}"${selected}>${escapeHtml(exercise.name)}</option>`;
                    }).join('');
                };

                const createExerciseRow = (index, values = {}) => {
                    const wrapper = document.createElement('div');
                    const selectedExerciseId = values.exercise_id ?? (exerciseOptions[0]?.id ?? '');
                    const orderIndex = values.order_index ?? (index + 1);
                    const setCount = values.set_count ?? defaultSetCount;

                    wrapper.innerHTML = template
                        .replaceAll('__INDEX__', index)
                        .replace('__EXERCISE_OPTIONS__', buildExerciseOptions(selectedExerciseId))
                        .replace('__ORDER_INDEX__', escapeHtml(orderIndex))
                        .replace('__SET_COUNT__', escapeHtml(setCount));

                    return wrapper.firstElementChild;
                };

                const ensureAtLeastOneExerciseRow = () => {
                    if (exerciseList.querySelectorAll('[data-exercise-row]').length === 0) {
                        exerciseList.appendChild(createExerciseRow(nextExerciseIndex));
                        nextExerciseIndex += 1;
                    }
                };

                addExerciseButton.addEventListener('click', () => {
                    exerciseList.appendChild(createExerciseRow(nextExerciseIndex));
                    nextExerciseIndex += 1;
                });

                exerciseList.addEventListener('click', (event) => {
                    const removeButton = event.target.closest('[data-remove-exercise]');

                    if (!removeButton) {
                        return;
                    }

                    const exerciseRow = removeButton.closest('[data-exercise-row]');

                    if (exerciseRow) {
                        exerciseRow.remove();
                    }

                    ensureAtLeastOneExerciseRow();
                });
            });
        </script>
    </body>
</html>

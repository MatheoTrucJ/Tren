<div class="p-6 space-y-4">
    <h1 class="text-2xl font-semibold">Workout POC (User {{ $userId }})</h1>

    @if($errorMessage !== null)
        <div class="rounded-lg border border-red-400/40 bg-red-950/30 px-4 py-3 text-red-100">
            {{ $errorMessage }}
        </div>
    @endif

    @if(count($workouts) === 0)
        <div class="rounded-lg border border-zinc-700 px-4 py-3">
            No workouts available.
        </div>
    @else
        <div class="grid gap-3">
            @foreach($workouts as $workout)
                <article class="rounded-lg border border-zinc-700 bg-zinc-900/60 p-4">
                    <h2 class="text-lg font-medium">{{ $workout['name'] }}</h2>
                    <p class="mt-1 text-sm text-zinc-300">
                        {{ $workout['description'] !== '' ? $workout['description'] : 'No description.' }}
                    </p>
                    <p class="mt-2 text-xs text-zinc-400">
                        Exercises: {{ $workout['exercise_count'] }}
                    </p>
                </article>
            @endforeach
        </div>
    @endif
</div>

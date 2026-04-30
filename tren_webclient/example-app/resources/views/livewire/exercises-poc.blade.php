<div class="p-6 space-y-4">
    <h1 class="text-2xl font-semibold">Exercises POC (User {{ $userId }})</h1>

    @if($errorMessage !== null)
        <div class="rounded-lg border border-red-400/40 bg-red-950/30 px-4 py-3 text-red-100">
            {{ $errorMessage }}
        </div>
    @endif

    @if(count($exercises) === 0)
        <div class="rounded-lg border border-zinc-700 px-4 py-3">
            No exercises available.
        </div>
    @else
        <div class="grid gap-3">
            @foreach($exercises as $exercise)
                <article class="rounded-lg border border-zinc-700 bg-zinc-900/60 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-medium">{{ $exercise['name'] }}</h2>
                        <span class="rounded-full px-2 py-1 text-xs {{ $exercise['is_personal'] ? 'bg-blue-500/20 text-blue-200' : 'bg-zinc-700 text-zinc-200' }}">
                            {{ $exercise['is_personal'] ? 'Personal' : 'General' }}
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-zinc-300">
                        {{ $exercise['description'] !== '' ? $exercise['description'] : 'No description.' }}
                    </p>
                </article>
            @endforeach
        </div>
    @endif
</div>

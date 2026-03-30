@props([
    'title',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 max-w-2xl text-sm text-slate-600">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>

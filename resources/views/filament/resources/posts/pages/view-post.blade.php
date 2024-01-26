<x-filament-panels::page>
    <x-filament::breadcrumbs :breadcrumbs="[
        route('filament.admin.resources.posts.index') => 'Posts',
        route('filament.admin.resources.posts.view', $record) => $record->title,
    ]" />

    <div class="prose dark:prose-invert min-w-full">
        <h1>{{ $record->title }}</h1>

        <img src="{{ asset("storage/{$record->cover->path}") }}" alt="{{ $record->cover->alt }}"
            title="{{ $record->cover->title }}">

        <div class="flex flex-col">
            <span>
                {{ $record->formattedPublishedAt ??
                    now()->locale('id')->settings(['formatFunction' => 'translatedFormat'])->format('l, j F Y h:i') }}
            </span>

            <span>Oleh: {{ $record->user->name }}</span>
        </div>

        <article>
            {!! $record->content !!}
        </article>

        <ul class="flex list-none justify-end gap-x-2">
            @foreach ($record->tags as $tag)
                <li class="lowercase">
                    <x-filament::badge color="primary">
                        {{ $tag->name }}
                    </x-filament::badge>
                </li>
            @endforeach
        </ul>
    </div>
</x-filament-panels::page>

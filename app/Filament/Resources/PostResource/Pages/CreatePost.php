<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected static bool $canCreateAnother = false;

    protected function getFormActions(): array
    {
        return [
            $this->getCreatePublishedFormAction(),
            $this->getCreateDraftedFormAction(),
            ...(static::canCreateAnother() ? [$this->getCreateAnotherFormAction()] : []),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreatePublishedFormAction(): Action
    {
        return Action::make('create-published')
            ->color(Color::Green)
            ->label('Publish')
            ->keyBindings(['ctrl+s'])
            ->action(function (): void {
                $this->data['status'] = 'published';

                $this->create();
            });
    }

    protected function getCreateDraftedFormAction(): Action
    {
        return Action::make('create-drafted')
            ->color(Color::Gray)
            ->label('Draft')
            ->keyBindings(['ctrl+d'])
            ->action(function (): void {
                $this->data['status'] = 'drafted';

                $this->create();
            });
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        match ($this->data['status']) {
            'published' => $data['published_at'] = now(),
            'drafted' => $data['published_at'] = null,
        };

        $data['user_id'] = filament()->auth()->id();

        foreach ($data['metas'] as $key => $value) {
            $data['metas'][] = compact('key', 'value');

            unset($data['metas'][$key]);
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $metas = $data['metas'];

        unset($data['metas']);

        $record = parent::handleRecordCreation($data);

        $record->metas()->createMany($metas);

        return $record;
    }
}

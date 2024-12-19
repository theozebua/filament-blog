<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->keyBindings([]),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSavePublishedFormAction(),
            $this->getSaveDraftedFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSavePublishedFormAction(): Action
    {
        return Action::make('save-published')
            ->color(Color::Green)
            ->label('Publish')
            ->keyBindings(['ctrl+s'])
            ->action(function (): void {
                $this->data['status'] = 'published';

                $this->save();
            });
    }

    protected function getSaveDraftedFormAction(): Action
    {
        return Action::make('save-drafted')
            ->color(Color::Gray)
            ->label('Draft')
            ->keyBindings(['ctrl+d'])
            ->action(function (): void {
                $this->data['status'] = 'drafted';

                $this->save();
            });
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return sprintf('Post %s!', $this->data['status']);
        ;
    }

    protected function authorizeAccess(): void
    {
        abort_if($this->getRecord()->trashed(), 404);
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    /**
     * @param Post $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->wrapInDatabaseTransaction(function () use ($record, $data): Model {
            $metas = $data['metas'];

            unset($data['metas']);

            foreach ($metas as $meta) {
                $currentMeta = $record->metas->where('key', $meta['key'])->first();

                if (!is_null($currentMeta)) {
                    if (is_null($meta['value'])) {
                        if ($meta['key'] === 'image') {
                            $storage = Storage::disk('public');
                            $oldImagePath = $currentMeta->value ?? '';

                            $storage->exists($oldImagePath) && $storage->delete($oldImagePath);
                        }

                        $currentMeta->delete();

                        continue;
                    }

                    $currentMeta->update(['value' => $meta['value']]);

                    continue;
                }

                if (!is_null($meta['value'])) {
                    $record->metas()->create([
                        'key' => $meta['key'],
                        'value' => $meta['value'],
                    ]);
                }
            }

            return parent::handleRecordUpdate($record, $data);
        });
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['metas'] = $this->getRecord()->metas->map->only(['key', 'value'])->toArray();

        foreach ($data['metas'] as $index => $meta) {
            ['key' => $key, 'value' => $value] = $meta;

            $data['metas'][$key] = $value;

            unset($data['metas'][$index]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}

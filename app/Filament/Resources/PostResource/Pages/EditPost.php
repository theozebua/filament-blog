<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->button()
                ->before(function (Post $record): void {
                    $record->update([
                        'published_at' => null,
                        'archived_at' => null,
                    ]);
                })
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

                $this->save(shouldSendSavedNotification: false);
                $this->getSavedNotification()->title('Post published!')->send();
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

                $this->save(shouldSendSavedNotification: false);
                $this->getSavedNotification()->title('Post drafted!')->send();
            });
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

        return $data;
    }
}

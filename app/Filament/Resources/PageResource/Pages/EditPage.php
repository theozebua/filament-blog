<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Models\Meta;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var \App\Models\Post $record */
        return DB::transaction(function () use ($record, $data): Model {
            $metas = [
                [
                    'key' => 'name',
                    'value' => 'title',
                    'content' => $data['meta_title'] ?? '',
                ],

                [
                    'key' => 'name',
                    'value' => 'description',
                    'content' => $data['meta_description'] ?? '',
                ],

                [
                    'key' => 'name',
                    'value' => 'keywords',
                    'content' => $data['meta_keywords'] ?? '',
                ],

                [
                    'key' => 'name',
                    'value' => 'og:image',
                    'content' => $data['meta_image'] ?? '',
                ],
            ];

            unset($data['meta_title']);
            unset($data['meta_description']);
            unset($data['meta_keywords']);
            unset($data['meta_image']);

            collect($metas)->each(function (array $meta) use ($record): void {
                $record->metas()->updateOrCreate($meta);
            });

            return parent::handleRecordUpdate($record, $data);
        });
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = $this->getRecord()->load(['metas']);

        $data->metas->each(function (Meta $meta) use ($data): void {
            $content = $meta->content;

            switch ($meta->value) {
                case 'title':
                    $data['meta_title'] = $content;
                    break;
                case 'description':
                    $data['meta_description'] = $content;
                    break;
                case 'keywords':
                    $data['meta_keywords'] = $content;
                    break;
                case 'og:image':
                    $data['meta_image'] = $content;
                    break;
            }
        });

        return $data->toArray();
    }
}

<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
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

            /** @var \App\Models\Page $page */
            $page = parent::handleRecordCreation($data);

            $page->metas()->createMany($metas);

            return $page->refresh();
        });
    }
}

<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Enums\Post\PostStatus;
use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;

class CreatePost extends CreateRecord
{
    protected HtmlSanitizer $htmlSanitizer;

    protected static string $resource = PostResource::class;

    public function __construct()
    {
        $this->htmlSanitizer = app(HtmlSanitizer::class);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): Model {
            $data['user_id'] = auth()->id();
            $data['published_at'] = $data['status'] === PostStatus::PUBLISHED->value ? now() : null;
            $data['content'] = $this->htmlSanitizer->sanitize($data['content']);

            $cover = $data['cover'];

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

            unset($data['cover']);
            unset($data['meta_title']);
            unset($data['meta_description']);
            unset($data['meta_keywords']);
            unset($data['meta_image']);

            /** @var \App\Models\Post $post */
            $post = parent::handleRecordCreation($data);

            $post->cover()->create($cover);
            $post->metas()->createMany($metas);

            return $post->refresh();
        });
    }
}

<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Enums\Post\PostStatus;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Actions\ArchiveAction;
use App\Filament\Resources\PostResource\Actions\DeleteAction;
use App\Filament\Resources\PostResource\Actions\ForceDeleteAction;
use App\Filament\Resources\PostResource\Actions\PublishAction;
use App\Filament\Resources\PostResource\Actions\RestoreAction;
use App\Filament\Resources\PostResource\Actions\UnarchiveAction;
use App\Filament\Resources\PostResource\Actions\UnpublishAction;
use App\Models\Meta;
use App\Models\Post;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;

class EditPost extends EditRecord
{
    protected HtmlSanitizer $htmlSanitizer;

    protected static string $resource = PostResource::class;

    public function __construct()
    {
        $this->htmlSanitizer = app(HtmlSanitizer::class);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var \App\Models\Post $record */
        return DB::transaction(function () use ($record, $data): Model {
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

            $oldCover = $record->cover?->path ?? '';

            if ($cover['path'] !== $oldCover) {
                $storage = Storage::disk('public');

                if ($storage->exists($oldCover)) {
                    $storage->delete($oldCover);
                }
            }

            $record->cover?->update($cover);

            collect($metas)->each(function (array $meta) use ($record): void {
                $record->metas()->updateOrCreate($meta);
            });

            return parent::handleRecordUpdate($record, $data);
        });
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Publish')
                ->requiresConfirmation()
                ->color(PostStatus::PUBLISHED->getColor())
                ->hidden(static fn (Post $post): bool => in_array($post->status, [PostStatus::PUBLISHED, PostStatus::ARCHIVED, PostStatus::TRASHED]))
                ->modalHeading('Publish Post')
                ->modalIcon('heroicon-o-check-circle')
                ->modalIconColor(PostStatus::PUBLISHED->getColor())
                ->action(static function (Post $post): void {
                    PublishAction::execute($post);
                }),

            Actions\Action::make('Unpublish')
                ->requiresConfirmation()
                ->color(PostStatus::DRAFTED->getColor())
                ->hidden(static fn (Post $post): bool => in_array($post->status, [PostStatus::DRAFTED, PostStatus::ARCHIVED, PostStatus::TRASHED]))
                ->modalHeading('Unpublish Post')
                ->modalIcon('heroicon-o-check-circle')
                ->modalIconColor(PostStatus::DRAFTED->getColor())
                ->action(static function (Post $post): void {
                    UnpublishAction::execute($post);
                }),

            Actions\Action::make('Archive')
                ->requiresConfirmation()
                ->color(PostStatus::ARCHIVED->getColor())
                ->hidden(static fn (Post $post): bool => in_array($post->status, [PostStatus::ARCHIVED, PostStatus::TRASHED]))
                ->modalHeading('Archive Post')
                ->modalIcon('heroicon-o-archive-box-arrow-down')
                ->modalIconColor(PostStatus::ARCHIVED->getColor())
                ->action(static function (Post $post): void {
                    ArchiveAction::execute($post);
                }),

            Actions\Action::make('Unarchive')
                ->requiresConfirmation()
                ->color(PostStatus::ARCHIVED->getColor())
                ->hidden(static fn (Post $post) => $post->status !== PostStatus::ARCHIVED)
                ->modalHeading('Unarchive Post')
                ->modalIcon('heroicon-o-archive-box-x-mark')
                ->modalIconColor(PostStatus::ARCHIVED->getColor())
                ->action(static function (Post $post): void {
                    UnarchiveAction::execute($post);
                }),

            Actions\RestoreAction::make()
                ->using(static function (Post $post): void {
                    RestoreAction::execute($post);
                }),

            Actions\DeleteAction::make()
                ->using(static function (Post $post): void {
                    DeleteAction::execute($post);
                }),

            Actions\ForceDeleteAction::make()
                ->using(static function (Post $post): void {
                    ForceDeleteAction::execute($post);
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = $this->getRecord()->load(['cover', 'metas']);

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

<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Enums\Post\PostStatus;
use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListPosts extends ListRecords
{
    protected Collection $counts;

    protected static string $resource = PostResource::class;

    public function __construct()
    {
        $this->counts = Post::withTrashed()
            ->whereUserId(auth()->id())
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
    }

    public function getTabs(): array
    {
        return [
            'published' => Tab::make('Published')
                ->badge($this->counts->get(PostStatus::PUBLISHED->value))
                ->badgeColor(PostStatus::PUBLISHED->getColor())
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->whereStatus(PostStatus::PUBLISHED);
                }),

            'drafted' => Tab::make('Drafted')
                ->badge($this->counts->get(PostStatus::DRAFTED->value))
                ->badgeColor(PostStatus::DRAFTED->getColor())
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->whereStatus(PostStatus::DRAFTED);
                }),

            'archived' => Tab::make('Archived')
                ->badge($this->counts->get(PostStatus::ARCHIVED->value))
                ->badgeColor(PostStatus::ARCHIVED->getColor())
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->whereStatus(PostStatus::ARCHIVED);
                }),

            'trashed' => Tab::make('Trashed')
                ->badge($this->counts->get(PostStatus::TRASHED->value))
                ->badgeColor(PostStatus::TRASHED->getColor())
                ->modifyQueryUsing(function (Builder $query): Builder {
                    return $query->whereStatus(PostStatus::TRASHED);
                }),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

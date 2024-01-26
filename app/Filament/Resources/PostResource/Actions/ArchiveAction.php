<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Actions;

use App\Contracts\HasExecutor;
use App\Enums\Post\PostStatus;
use Illuminate\Database\Eloquent\Model;

class ArchiveAction implements HasExecutor
{
    public static function execute(Model $model): mixed
    {
        return $model->update([
            'status' => PostStatus::ARCHIVED,
            'archived_at' => now(),
        ]);
    }
}

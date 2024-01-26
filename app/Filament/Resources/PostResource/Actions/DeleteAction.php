<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Actions;

use App\Contracts\HasExecutor;
use App\Enums\Post\PostStatus;
use Illuminate\Database\Eloquent\Model;

class DeleteAction implements HasExecutor
{
    public static function execute(Model $model): mixed
    {
        $model->delete();

        return $model->update([
            'status' => PostStatus::TRASHED,
            'archived_at' => null,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Actions;

use App\Contracts\HasExecutor;
use App\Enums\Post\PostStatus;
use Illuminate\Database\Eloquent\Model;

class UnpublishAction implements HasExecutor
{
    public static function execute(Model $model): mixed
    {
        return $model->update([
            'status' => PostStatus::DRAFTED,
            'published_at' => null,
        ]);
    }
}

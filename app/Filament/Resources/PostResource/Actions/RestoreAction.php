<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Actions;

use App\Contracts\HasExecutor;
use App\Enums\Post\PostStatus;
use Illuminate\Database\Eloquent\Model;

class RestoreAction implements HasExecutor
{
    public static function execute(Model $model): mixed
    {
        $model->restore();

        return $model->update([
            'status' => PostStatus::DRAFTED,
        ]);
    }
}

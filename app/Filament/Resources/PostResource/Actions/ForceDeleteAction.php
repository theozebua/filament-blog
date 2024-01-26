<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Actions;

use App\Contracts\HasExecutor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ForceDeleteAction implements HasExecutor
{
    public static function execute(Model $model): mixed
    {
        return DB::transaction(function () use ($model): ?bool {
            $cover = $model->cover;

            $model->categories()->detach();

            $deleted = $model->forceDelete();

            $storage = Storage::disk('public');

            if ($storage->exists($cover->path)) {
                $storage->delete($cover->path);
            }

            $cover->delete();

            return $deleted;
        });
    }
}

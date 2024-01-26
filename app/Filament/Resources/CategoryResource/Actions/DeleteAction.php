<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Actions;

use App\Contracts\HasExecutor;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class DeleteAction implements HasExecutor
{
    public static function execute(Model $model): mixed
    {
        try {
            return $model->delete();
        } catch (QueryException) {
            Notification::make()
                ->title('Cannot delete this category. It still being used by some posts.')
                ->warning()
                ->send();

            return null;
        }
    }
}

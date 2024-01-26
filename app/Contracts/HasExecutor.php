<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface HasExecutor
{
    public static function execute(Model $model): mixed;
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Meta extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    public function metaable(): MorphTo
    {
        return $this->morphTo();
    }
}

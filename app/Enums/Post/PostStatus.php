<?php

declare(strict_types=1);

namespace App\Enums\Post;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;

enum PostStatus: string implements HasColor
{
    case DRAFTED = 'drafted';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case TRASHED = 'trashed';

    public static function values(): array
    {
        return collect(self::cases())->map(function (self $status): string {
            return $status->value;
        })->toArray();
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(function (self $status): array {
                return [
                    $status->title() => $status->value,
                ];
            })
            ->collapse()
            ->flip()
            ->forget([self::TRASHED->value, self::ARCHIVED->value])
            ->toArray();
    }

    public function title(): string
    {
        return str($this->name)->title()->replace(' ', '')->value();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DRAFTED => Color::Zinc,
            self::PUBLISHED => 'success',
            self::ARCHIVED => 'warning',
            self::TRASHED => 'danger',
        };
    }
}

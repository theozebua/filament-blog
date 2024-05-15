<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique('categories', 'name', ignoreRecord: true)
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state = null) => $set('slug', str($state ?? '')->slug())),

                        Forms\Components\TextInput::make('slug')
                            ->helperText('A slug is a short, descriptive URL part, like "top-10-travel-destinations" for "Top 10 Travel Destinations."')
                            ->required()
                            ->maxLength(255)
                            ->unique('categories', 'slug', ignoreRecord: true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button(),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->using(function (Category $record): void {
                        // Delete relations to posts before deleting the category
                        $record->posts()->detach();
                        $record->delete();
                    }),
            ])
            ->emptyStateIcon(static::$navigationIcon)
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('New category')
                    ->url(static::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}

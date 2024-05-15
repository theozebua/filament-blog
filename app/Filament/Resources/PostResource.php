<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('posts', 'title', ignoreRecord: true)
                                    ->live(debounce: 500)
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state = null) => $set('slug', str($state ?? '')->slug())),

                                Forms\Components\TextInput::make('slug')
                                    ->helperText('A slug is a short, descriptive URL part, like "top-10-travel-destinations" for "Top 10 Travel Destinations."')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('posts', 'slug', ignoreRecord: true),

                                Forms\Components\RichEditor::make('body')
                                    ->label('Content')
                                    ->columnSpanFull()
                                    ->required(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan([
                        'lg' => 2,
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\SpatieMediaLibraryFileUpload::make('cover')
                                    ->collection('covers')
                                    ->disk('public')
                                    ->customProperties(
                                        fn (Forms\Get $get): array => [
                                            'alt' => $get('title'),
                                        ],
                                    )
                                    ->imageEditor()
                                    ->required()
                                    ->image(),

                                Forms\Components\Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->options(Category::pluck('name', 'id'))
                                    ->optionsLimit(5)
                                    ->required(),

                                Forms\Components\SpatieTagsInput::make('tags'),
                            ]),

                        // TODO: Meta input fields for SEO
                    ])
                    ->columnSpan([
                        'lg' => 1,
                    ]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('cover')
                    ->collection('covers')
                    ->disk('public')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('author.name')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('publish')
                    ->button()
                    ->color(Color::Green)
                    ->visible(function (Post $record): bool {
                        return $record->drafted();
                    })
                    ->action(function (Post $record): Post {
                        return $record->publish();
                    })
                    ->after(function (Tables\Actions\Action $action): void {
                        $action->sendSuccessNotification();
                    })
                    ->successNotificationTitle('Post published!'),

                Tables\Actions\Action::make('draft')
                    ->button()
                    ->color(Color::Gray)
                    ->visible(function (Post $record): bool {
                        return $record->published();
                    })
                    ->action(function (Post $record): Post {
                        return $record->draft();
                    })
                    ->after(function (Tables\Actions\Action $action): void {
                        $action->sendSuccessNotification();
                    })
                    ->successNotificationTitle('Post drafted!'),

                Tables\Actions\EditAction::make()
                    ->button()
                    ->hidden(function (Post $record): bool {
                        return $record->trashed();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->before(function (Post $record): void {
                        $record->update([
                            'published_at' => null,
                            'archived_at' => null,
                        ]);
                    }),

                Tables\Actions\RestoreAction::make()
                    ->button(),

                Tables\Actions\ForceDeleteAction::make()
                    ->button()
                    ->before(function (Post $record): void {
                        // Delete relations to categories before deleting the post
                        $record->categories()->detach();
                    }),
            ])
            ->emptyStateIcon(static::$navigationIcon)
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('New post')
                    ->url(static::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->recordUrl(null);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope(SoftDeletingScope::class);
    }
}

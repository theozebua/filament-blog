<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('post-attachments')
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
                                    ->required()
                                    ->createOptionForm(CategoryResource::form($form)->getComponents())
                                    ->createOptionAction(function (Forms\Components\Actions\Action $action): Forms\Components\Actions\Action {
                                        return $action->extraModalFooterActions([]);
                                    }),

                                Forms\Components\SpatieTagsInput::make('tags'),
                            ]),

                        Forms\Components\Section::make('SEO')
                            ->schema([
                                Forms\Components\TextInput::make('metas.title')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('metas.keywords')
                                    ->helperText('Comma separated list of keywords')
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('metas.description')
                                    ->rows(4)
                                    ->helperText(function (?string $state = null): string {
                                        return sprintf('Max %d/160 characters', strlen($state ?? ''));
                                    })
                                    ->live(debounce: 500)
                                    ->maxLength(160),

                                Forms\Components\FileUpload::make('metas.image')
                                    ->directory('meta-images')
                                    ->disk('public')
                                    ->imageEditor()
                                    ->image(),
                            ])
                            ->collapsible()
                            ->collapsed(function (?Post $record = null): bool {
                                return $record?->metas->isEmpty() ?? true;
                            }),
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

                Tables\Columns\ToggleColumn::make('published_at')
                    ->label('Published')
                    ->updateStateUsing(function (Post $record, mixed $state): void {
                        if ($state) {
                            $record->publish();
                        } else {
                            $record->draft();
                        }
                    })
                    ->afterStateUpdated(function (mixed $state): void {
                        $notification = Notification::make('change-status-success');

                        if ($state) {
                            $notification
                                ->success()
                                ->title('Post published!');
                        } else {
                            $notification
                                ->success()
                                ->title('Post drafted!');
                        }

                        $notification->send();
                    })
                    ->onColor(Color::Green)
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(function (Post $record): bool {
                        return $record->trashed();
                    }),

                Tables\Actions\DeleteAction::make(),

                Tables\Actions\RestoreAction::make(),

                Tables\Actions\ForceDeleteAction::make()
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
                    ->icon('heroicon-m-plus'),
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

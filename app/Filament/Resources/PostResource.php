<?php

namespace App\Filament\Resources;

use App\Enums\Post\PostStatus;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\Actions;
use App\Models\Category;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationGroup = 'Article';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Article')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug()))
                            ->live(onBlur: true),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\RichEditor::make('content')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('posts/attachment')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Informations')
                    ->schema([
                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->options(Category::all()->pluck('name', 'id'))
                            ->required()
                            ->createOptionAction(function (Action $action): Action {
                                return $action
                                    ->modalHeading('Create category')
                                    ->modalSubmitActionLabel('Create')
                                    ->modalWidth('lg');
                            })
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug()))
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        Forms\Components\SpatieTagsInput::make('tags'),

                        Forms\Components\Select::make('status')
                            ->options(PostStatus::options())
                            ->default(PostStatus::DRAFTED->value)
                            ->selectablePlaceholder(false)
                            ->required()
                            ->hidden(fn (Post $post) => in_array($post->status, [PostStatus::ARCHIVED, PostStatus::TRASHED])),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('cover.path')
                            ->label('Cover')
                            ->disk('public')
                            ->directory('covers')
                            ->imageEditor()
                            ->rules(['mimes:png,jpg,jpeg,webp'])
                            ->image()
                            ->required(),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('cover.title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('cover.alt')
                                    ->label('Alt Text')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(2),
                    ]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->label('Title')
                                    ->required(),

                                Forms\Components\TextInput::make('meta_keywords')
                                    ->label('Keywords')
                                    ->required(),
                            ])
                            ->columns(2),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Textarea::make('meta_description')
                                    ->label('Description')
                                    ->rows(5)
                                    ->required(),

                                Forms\Components\FileUpload::make('meta_image')
                                    ->disk('public')
                                    ->directory('meta-images')
                                    ->imageEditor()
                                    ->rules(['mimes:png,jpg,jpeg,webp'])
                                    ->image()
                                    ->required(),
                            ])
                            ->columns(2),
                    ])
                    ->collapsible()
                    ->heading('SEO'),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Repeater::make('meta')
                            ->relationship('metas', function (Builder $query): void {
                                $query->where('value', '!=', 'title')
                                    ->where('value', '!=', 'description')
                                    ->where('value', '!=', 'keywords')
                                    ->where('value', '!=', 'og:image');
                            })
                            ->label('')
                            ->itemLabel(fn (array $state): ?string => $state['value'] ?? 'Meta')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('Attribute')
                                    ->helperText(
                                        str('The key that the meta tag should use as attribute name, such as **name**, **property** or **custom attribute**.')
                                            ->markdown()
                                            ->toHtmlString(),
                                    )
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('value')
                                    ->helperText(
                                        str('The value that the meta tag should use as value of the attribute, such as **title**, **description**, **og:image**, etc.')
                                            ->markdown()
                                            ->toHtmlString(),
                                    )
                                    ->live(true)
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('content')
                                    ->helperText(
                                        str('The content that the meta tag should use.')
                                            ->markdown()
                                            ->toHtmlString(),
                                    )
                                    ->required(),
                            ])
                            ->reorderableWithDragAndDrop(false)
                            ->default([])
                            ->collapsible()
                            ->addActionLabel('+ Add')
                            ->grid(2),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->heading('Additional Meta'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover.path'),

                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PostStatus $state) => $state->getColor())
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable()
                    ->label('Author'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hidden(static fn (Post $post) => in_array($post->status, [PostStatus::ARCHIVED, PostStatus::TRASHED])),

                Tables\Actions\Action::make('Publish')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-check-circle')
                    ->color(PostStatus::PUBLISHED->getColor())
                    ->hidden(static fn (Post $post): bool => in_array($post->status, [PostStatus::PUBLISHED, PostStatus::ARCHIVED, PostStatus::TRASHED]))
                    ->modalHeading('Publish post')
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalIconColor(PostStatus::PUBLISHED->getColor())
                    ->action(static function (Post $post): void {
                        Actions\PublishAction::execute($post);
                    }),

                Tables\Actions\Action::make('Unpublish')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-document')
                    ->color(PostStatus::DRAFTED->getColor())
                    ->hidden(static fn (Post $post): bool => in_array($post->status, [PostStatus::DRAFTED, PostStatus::ARCHIVED, PostStatus::TRASHED]))
                    ->modalHeading('Unpublish post')
                    ->modalIcon('heroicon-o-document')
                    ->modalIconColor(PostStatus::DRAFTED->getColor())
                    ->action(static function (Post $post): void {
                        Actions\UnpublishAction::execute($post);
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Archive')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-archive-box-arrow-down')
                        ->color(PostStatus::ARCHIVED->getColor())
                        ->hidden(static fn (Post $post): bool => in_array($post->status, [PostStatus::ARCHIVED, PostStatus::TRASHED]))
                        ->modalHeading('Archive post')
                        ->modalIcon('heroicon-o-archive-box-arrow-down')
                        ->modalIconColor(PostStatus::ARCHIVED->getColor())
                        ->action(static function (Post $post): void {
                            Actions\ArchiveAction::execute($post);
                        }),

                    Tables\Actions\Action::make('Unarchive')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-archive-box-x-mark')
                        ->color(PostStatus::ARCHIVED->getColor())
                        ->hidden(static fn (Post $post) => $post->status !== PostStatus::ARCHIVED)
                        ->modalHeading('Unarchive post')
                        ->modalIcon('heroicon-o-archive-box-x-mark')
                        ->modalIconColor(PostStatus::ARCHIVED->getColor())
                        ->action(static function (Post $post): void {
                            Actions\UnarchiveAction::execute($post);
                        }),

                    Tables\Actions\EditAction::make()
                        ->hidden(static fn (Post $post): bool => $post->status === PostStatus::TRASHED),

                    Tables\Actions\DeleteAction::make()
                        ->using(static function (Post $post): void {
                            Actions\DeleteAction::execute($post);
                        }),

                    Tables\Actions\ForceDeleteAction::make()
                        ->using(static function (Post $post): void {
                            Actions\ForceDeleteAction::execute($post);
                        }),

                    Tables\Actions\RestoreAction::make()
                        ->using(static function (Post $post): void {
                            Actions\RestoreAction::execute($post);
                        }),
                ]),
            ])
            ->emptyStateIcon(static::$navigationIcon)
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('New post')
                    ->url(static::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);
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
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereUserId(auth()->id())
            ->withTrashed()
            ->latest();
    }
}

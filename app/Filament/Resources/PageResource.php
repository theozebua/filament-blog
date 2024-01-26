<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled()
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str($state)->slug()))
                            ->live(onBlur: true),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(),

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
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->emptyStateIcon(static::$navigationIcon);
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}

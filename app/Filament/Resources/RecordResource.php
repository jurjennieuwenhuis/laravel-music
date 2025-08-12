<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecordResource\Pages;
use App\Infolists\Components\SpotifyAlbumPlayer;
use App\Models\Record;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecordResource extends Resource
{
    protected static ?string $model = Record::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Select::make('artist_id')
                                    ->relationship('artists', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\MarkdownEditor::make('bio')
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('url')
                                            ->label('URL')
                                            ->helperText('For example a link to Genius')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('spotify_code')
                                            ->helperText('Paste the spotify album code or the whole url found in Spotify.')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        Forms\Components\SpatieMediaLibraryFileUpload::make('media')
                                            ->collection('artist-images')
                                            ->hiddenLabel(),
                                    ]),

                                Forms\Components\MarkdownEditor::make('description')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->helperText('For example a link to Genius')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('spotify_code')
                                    ->helperText('Paste the spotify album code or the whole url found in Spotify.')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('type')
                                    ->options(['Single' => 'Single', 'EP' => 'EP', 'LP' => 'LP'])
                                    ->required(),
                            ]),
                        Forms\Components\Section::make('Images')
                            ->schema([
                                Forms\Components\SpatieMediaLibraryFileUpload::make('media')
                                    ->collection('record-images')
                                    ->multiple()
                                    ->reorderable()
                                    ->maxFiles(10)
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status')
                            ->schema([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->default(true),

                                Forms\Components\DatePicker::make('release_date')
                                    ->label('Release Date')
                                    ->default(now())
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Taxonomy')
                            ->schema([
                                Forms\Components\Select::make('genre_id')
                                    ->relationship('genres', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required(),
                                    ])
                                ,
                            ])
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\SpatieMediaLibraryImageColumn::make('record-image')
                        ->label('Image')
                        ->height('100%')
                        ->width('100%')
                        ->limit(1)
                        ->collection('record-images'),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\Layout\Split::make([
                            Tables\Columns\IconColumn::make('is_visible')
                                ->grow(false)
                                ->label('Visibility'),

                            Tables\Columns\TextColumn::make('name')
                                ->sortable()
                                ->searchable()
                                ->weight(FontWeight::Bold)
                        ])
                    ]),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('artists.name')
                            ->separator(', ')
                            ->weight(FontWeight::Light)
                            ->prefix('By: ')
                            ->searchable(),
                    ]),
                ])
                ->space(3),

                Tables\Columns\Layout\Panel::make([
                    Tables\Columns\TextColumn::make('release_date')
                        ->prefix('Release Date: ')
                        ->sortable()
                        ->date(),
                    Tables\Columns\TextColumn::make('type')
                        ->prefix('Type: '),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('genres.name')
                            ->prefix('Genres: ')
                            ->separator(', ')
                            ->searchable(),
                    ]),

                ])->collapsible()
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->defaultSort('name', 'asc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->paginated([
                18,
                36,
                72,
                'all',
            ])
            ->actions([
                Tables\Actions\Action::make('url')
                    ->label('Visit link')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->hidden(fn (Record $record): bool => empty($record->url))
                    ->url(fn (Record $record): ?string => $record->url)
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
                Tables\Actions\RestoreAction::make()
                    ->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel()
                    ->icon(null)
                    ->modalHeading(fn (Record $record): string => $record->name)
                    //->modalCloseButton(false)
                    ->slideOver(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
        ;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\SpatieMediaLibraryImageEntry::make('record-images')
                            ->hiddenLabel()
                            ->width('51%')
                            ->height('51%')
                            ->collection('record-images'),
                    ])
                    ->columnSpan(['lg' => 1]),
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),

                        Infolists\Components\TextEntry::make('artists.name')
                            ->separator(', '),

                        Infolists\Components\TextEntry::make('description')
                            ->hidden(fn (Record $record): bool => empty($record->description))
                            ->markdown()
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('genres.name')
                            ->separator(', '),

                        Infolists\Components\TextEntry::make('release_date')
                            ->label('Release Date')
                            ->date(),

                        Infolists\Components\TextEntry::make('url')
                            ->label('URL')
                            ->columnSpanFull()
                            ->url(fn (Record $record): string => $record->url),

                        SpotifyAlbumPlayer::make('spotify_code')
                            ->label('Listen')
                            ->columnSpanFull()
                            ->hidden(fn (Record $record): bool => empty($record->spotify_code)),
                    ])
                    ->columnSpan(['lg' => 2]),
            ])
            ->columns(3);
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
            'index' => Pages\ListRecords::route('/'),
            'create' => Pages\CreateRecord::route('/create'),
            //'view' => Pages\ViewRecord::route('/{record}'),
            'edit' => Pages\EditRecord::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = static::$model;

        return (string) $modelClass::where('is_visible', true)->count();
    }
}

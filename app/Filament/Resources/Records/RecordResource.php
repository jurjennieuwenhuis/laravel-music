<?php

namespace App\Filament\Resources\Records;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\Records\Pages\ListRecords;
use App\Filament\Resources\Records\Pages\CreateRecord;
use App\Filament\Resources\Records\Pages\EditRecord;
use App\Infolists\Components\SpotifyAlbumPlayer;
use App\Models\Record;
use Filament\Forms;
use Filament\Infolists;
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

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('artist_id')
                                    ->relationship('artists', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        MarkdownEditor::make('bio')
                                            ->columnSpanFull(),

                                        TextInput::make('url')
                                            ->label('URL')
                                            ->helperText('For example a link to Genius')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('spotify_code')
                                            ->helperText('Paste the spotify album code or the whole url found in Spotify.')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        SpatieMediaLibraryFileUpload::make('media')
                                            ->collection('artist-images')
                                            ->hiddenLabel(),
                                    ]),

                                MarkdownEditor::make('description')
                                    ->columnSpanFull(),

                                TextInput::make('url')
                                    ->label('URL')
                                    ->helperText('For example a link to Genius')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('spotify_code')
                                    ->helperText('Paste the spotify album code or the whole url found in Spotify.')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Select::make('type')
                                    ->options(['Single' => 'Single', 'EP' => 'EP', 'LP' => 'LP'])
                                    ->required(),
                            ]),
                        Section::make('Images')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('media')
                                    ->collection('record-images')
                                    ->multiple()
                                    ->reorderable()
                                    ->maxFiles(10)
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Group::make()
                    ->schema([
                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->default(true),

                                DatePicker::make('release_date')
                                    ->label('Release Date')
                                    ->default(now())
                                    ->required(),
                            ]),

                        Section::make('Taxonomy')
                            ->schema([
                                Select::make('genre_id')
                                    ->relationship('genres', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
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
                Stack::make([
                    SpatieMediaLibraryImageColumn::make('record-image')
                        ->label('Image')
                        ->imageSize('100%')
                        ->limit(1)
                        ->collection('record-images'),

                    Stack::make([
                        Split::make([
                            IconColumn::make('is_visible')
                                ->grow(false)
                                ->label('Visibility'),

                            TextColumn::make('name')
                                ->sortable()
                                ->searchable()
                                ->weight(FontWeight::Bold)
                        ])
                    ]),

                    Stack::make([
                        TextColumn::make('artists.name')
                            ->separator(', ')
                            ->weight(FontWeight::Light)
                            ->prefix('By: ')
                            ->searchable(),
                    ]),
                ])
                ->space(3),

                Panel::make([
                    TextColumn::make('release_date')
                        ->prefix('Release Date: ')
                        ->sortable()
                        ->date(),
                    TextColumn::make('type')
                        ->prefix('Type: '),

                    Stack::make([
                        TextColumn::make('genres.name')
                            ->prefix('Genres: ')
                            ->separator(', ')
                            ->searchable(),
                    ]),

                ])->collapsible()
            ])
            ->filters([
                TrashedFilter::make(),
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
            ->recordActions([
                Action::make('url')
                    ->label('Visit link')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->hidden(fn (Record $record): bool => empty($record->url))
                    ->url(fn (Record $record): ?string => $record->url)
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->hiddenLabel(),
                RestoreAction::make()
                    ->hiddenLabel(),
                ViewAction::make()
                    ->hiddenLabel()
                    ->icon(null)
                    ->modalHeading(fn (Record $record): string => $record->name)
                    //->modalCloseButton(false)
                    ->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->recordUrl(null)
        ;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('record-images')
                            ->hiddenLabel()
                            ->width('51%')
                            ->height('51%')
                            ->collection('record-images'),
                    ])
                    ->columnSpan(['lg' => 1]),
                Group::make()
                    ->schema([
                        TextEntry::make('name'),

                        TextEntry::make('artists.name')
                            ->separator(', '),

                        TextEntry::make('description')
                            ->hidden(fn (Record $record): bool => empty($record->description))
                            ->markdown()
                            ->columnSpanFull(),

                        TextEntry::make('genres.name')
                            ->separator(', '),

                        TextEntry::make('release_date')
                            ->label('Release Date')
                            ->date(),

                        TextEntry::make('url')
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
            'index' => ListRecords::route('/'),
            'create' => CreateRecord::route('/create'),
            //'view' => Pages\ViewRecord::route('/{record}'),
            'edit' => EditRecord::route('/{record}/edit'),
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

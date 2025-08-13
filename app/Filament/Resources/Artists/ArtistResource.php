<?php

namespace App\Filament\Resources\Artists;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\Artists\Pages\ListArtists;
use App\Infolists\Components\SpotifyArtistPlayer;
use App\Models\Artist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static ?string $slug = 'artists';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

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
                            ]),
                        Section::make('Images')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('media')
                                    ->collection('artist-images')
                                    ->hiddenLabel(),
                            ])
                            ->collapsible(),
                    ])
                    ->columnSpan(['lg' => 3]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    SpatieMediaLibraryImageColumn::make('artist-image')
                        ->label('Image')
                        ->imageSize('100%')
                        ->collection('artist-images'),

                    Stack::make([
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::Bold),
                    ])
                ]),
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
                    ->hidden(fn (Artist $record): bool => empty($record->url))
                    ->url(fn (Artist $record): ?string => $record->url)
                    ->openUrlInNewTab(),

                EditAction::make()->hiddenLabel()->slideOver(),
                RestoreAction::make()->hiddenLabel(),
                ViewAction::make()
                    ->hiddenLabel()
                    ->icon(null)
                    ->modalHeading(fn (Artist $record): string => $record->name)
                    //->modalCloseButton(false)
                    ->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('artist-image')
                            ->hiddenLabel()
                            ->width('51%')
                            ->height('51%')
                            ->collection('artist-images'),
                    ])
                    ->columnSpan(['lg' => 1]),

                Group::make()
                    ->schema([
                        TextEntry::make('name'),

                        TextEntry::make('bio')
                            ->hidden(fn (Artist $record): bool => empty($record->bio))
                            ->markdown()
                            ->columnSpanFull(),

                        SpotifyArtistPlayer::make('spotify_code')
                            ->label('Listen')
                            ->columnSpanFull()
                            ->hidden(fn (Artist $record): bool => empty($record->spotify_code)),
                    ])
                    ->columnSpan(['lg' => 2]),
            ])
            ->columns(3);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArtists::route('/'),
            //'create' => Pages\CreateArtist::route('/create'),
            //'edit' => Pages\EditArtist::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}

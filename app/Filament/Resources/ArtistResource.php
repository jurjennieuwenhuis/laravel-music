<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistResource\Pages;
use App\Infolists\Components\SpotifyAlbumPlayer;
use App\Infolists\Components\SpotifyArtistPlayer;
use App\Models\Artist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static ?string $slug = 'artists';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

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
                            ]),
                        Forms\Components\Section::make('Images')
                            ->schema([
                                Forms\Components\SpatieMediaLibraryFileUpload::make('media')
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
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\SpatieMediaLibraryImageColumn::make('artist-image')
                        ->label('Image')
                        ->height('100%')
                        ->width('100%')
                        ->collection('artist-images'),

                    Tables\Columns\Layout\Stack::make([
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
            ->actions([
                Tables\Actions\Action::make('url')
                    ->label('Visit link')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->hidden(fn (Artist $record): bool => empty($record->url))
                    ->url(fn (Artist $record): ?string => $record->url)
                    ->openUrlInNewTab(),

                EditAction::make()->hiddenLabel()->slideOver(),
                RestoreAction::make()->hiddenLabel(),
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel()
                    ->icon(null)
                    ->modalHeading(fn (Artist $record): string => $record->name)
                    //->modalCloseButton(false)
                    ->slideOver(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\SpatieMediaLibraryImageEntry::make('artist-image')
                            ->hiddenLabel()
                            ->width('51%')
                            ->height('51%')
                            ->collection('artist-images'),
                    ])
                    ->columnSpan(['lg' => 1]),

                Infolists\Components\Group::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('name'),

                        Infolists\Components\TextEntry::make('bio')
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
            'index' => Pages\ListArtists::route('/'),
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

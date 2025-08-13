<?php

namespace App\Filament\Resources\Genres\Pages;

use Filament\Support\Enums\Width;
use App\Filament\Resources\Genres\GenreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGenres extends ListRecords
{
    protected static string $resource = GenreResource::class;

    protected Width|string|null $maxContentWidth = Width::ExtraLarge->value;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}

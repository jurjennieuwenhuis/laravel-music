<?php

namespace App\Filament\Resources\Records\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Records\RecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords as BaseListRecords;

class ListRecords extends BaseListRecords
{
    protected static string $resource = RecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

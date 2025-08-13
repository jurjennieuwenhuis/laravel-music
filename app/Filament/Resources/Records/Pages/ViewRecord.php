<?php

namespace App\Filament\Resources\Records\Pages;

use Filament\Actions\EditAction;
use App\Filament\Resources\Records\RecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord as BaseViewRecord;

class ViewRecord extends BaseViewRecord
{
    protected static string $resource = RecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\AccountabilityPartnerRequests\Pages;

use App\Filament\Resources\AccountabilityPartnerRequests\AccountabilityPartnerRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountabilityPartnerRequests extends ListRecords
{
    protected static string $resource = AccountabilityPartnerRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

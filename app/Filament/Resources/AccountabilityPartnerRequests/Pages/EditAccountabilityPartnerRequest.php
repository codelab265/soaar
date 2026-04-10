<?php

namespace App\Filament\Resources\AccountabilityPartnerRequests\Pages;

use App\Filament\Resources\AccountabilityPartnerRequests\AccountabilityPartnerRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccountabilityPartnerRequest extends EditRecord
{
    protected static string $resource = AccountabilityPartnerRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

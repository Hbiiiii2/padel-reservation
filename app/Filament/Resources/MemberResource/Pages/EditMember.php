<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['password_confirmation']);

        if (empty($data['membership_code'])) {
            $data['membership_code'] = $this->record->membership_code ?? User::generateMembershipCode();
        }

        $data['is_admin'] = false;

        return $data;
    }
}


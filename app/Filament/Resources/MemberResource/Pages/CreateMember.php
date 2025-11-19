<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_admin'] = false;
        $data['password'] = Hash::make($data['password']);
        $data['membership_code'] = $data['membership_code'] ?: User::generateMembershipCode();

        unset($data['password_confirmation']);

        return $data;
    }
}


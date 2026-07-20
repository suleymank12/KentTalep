<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Arr;

final class UpdateProfile
{
    /**
     * Kullanıcının kendi ad/telefon bilgisini günceller.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): User
    {
        $user->fill(Arr::only($data, ['name', 'phone']));
        $user->save();

        return $user;
    }
}

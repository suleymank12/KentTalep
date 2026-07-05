<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Rol enum'undaki her case için 'web' guard'lı rol oluşturur.
     * Idempotent'tir: mevcut roller yeniden oluşturulmaz.
     */
    public function run(): void
    {
        foreach (RoleEnum::cases() as $role) {
            Role::firstOrCreate([
                'name' => $role->value,
                'guard_name' => 'web',
            ]);
        }
    }
}

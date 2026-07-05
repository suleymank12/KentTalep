<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Yalnızca yerel geliştirme içindir: rolleri ve her rolden birer örnek
 * kullanıcı üretir. Production kurulumunda RoleSeeder + kenttalep:admin
 * komutu kullanılır (bkz. README).
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $role) {
            User::factory()
                ->create([
                    'name' => $role->label().' Kullanıcı',
                    'email' => $role->value.'@kenttalep.test',
                ])
                ->assignRole($role->value);
        }
    }
}

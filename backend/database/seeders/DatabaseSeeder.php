<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role as RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Yalnızca yerel geliştirme içindir: roller, kategoriler, her rolden birer
 * örnek kullanıcı, ek personel havuzu ve ~50 demo talep üretir. Production
 * kurulumunda RoleSeeder + CategorySeeder + kenttalep:admin kullanılır
 * (bkz. README).
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CategorySeeder::class,
            SettingsSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RoleEnum::cases() as $role) {
            User::factory()
                ->create([
                    'name' => $role->label().' Kullanıcı',
                    'email' => $role->value.'@kenttalep.test',
                ])
                ->assignRole($role->value);
        }

        // Atama/saha akışı için ek personel havuzu.
        User::factory(6)
            ->create()
            ->each(fn (User $staff) => $staff->assignRole(RoleEnum::Staff->value));

        $this->call(TicketSeeder::class);
    }
}

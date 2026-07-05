<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Admin rolünde bir kullanıcı oluşturur; e-posta zaten varsa günceller.
 * Production kurulumunda ilk admin bu komutla açılır. name/email/password
 * seçenek olarak verilmezse etkileşimli olarak sorulur (şifre gizli girilir).
 */
class CreateAdminUser extends Command
{
    protected $signature = 'kenttalep:admin {--name=} {--email=} {--password=}';

    protected $description = 'Admin rolünde kullanıcı oluşturur veya mevcutsa günceller';

    public function handle(): int
    {
        $name = $this->optionString('name') ?? text('İsim', required: true);
        $email = $this->optionString('email') ?? text('E-posta', required: true);
        $secret = $this->optionString('password') ?? password('Şifre', required: true);

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $secret],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $existed = $user->exists;

        $user->fill([
            'name' => $name,
            'password' => Hash::make($secret),
            'is_active' => true,
        ]);
        $user->save();

        if ($user->trashed()) {
            $user->restore();
        }

        $user->syncRoles([Role::Admin->value]);

        $this->info(sprintf(
            'Admin kullanıcı %s: %s',
            $existed ? 'güncellendi' : 'oluşturuldu',
            $email,
        ));

        return self::SUCCESS;
    }

    private function optionString(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) ? $value : null;
    }
}

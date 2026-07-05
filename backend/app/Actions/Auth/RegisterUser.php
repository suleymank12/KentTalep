<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\DevicePlatform;
use App\Enums\Role;
use App\Models\User;

final class RegisterUser
{
    public function __construct(private readonly IssueDeviceToken $issueDeviceToken) {}

    /**
     * Yeni bir vatandaş hesabı oluşturur, citizen rolü atar ve ilk cihaz
     * token'ını üretir.
     *
     * @return array{user: User, token: string}
     */
    public function handle(
        string $name,
        string $email,
        ?string $phone,
        string $password,
        string $deviceName,
        DevicePlatform $platform,
    ): array {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'is_active' => true,
        ]);

        $user->assignRole(Role::Citizen->value);

        $issued = $this->issueDeviceToken->handle($user, $deviceName, $platform);

        return [
            'user' => $user,
            'token' => $issued['token'],
        ];
    }
}

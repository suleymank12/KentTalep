<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\DevicePlatform;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class LoginUser
{
    public function __construct(private readonly IssueDeviceToken $issueDeviceToken) {}

    /**
     * Kimlik bilgilerini doğrular ve yeni bir cihaz token'ı üretir.
     * Hatalı bilgide 422 genel mesaj; pasif hesapta 403 döner.
     *
     * @return array{user: User, token: string}
     */
    public function handle(
        string $email,
        string $password,
        string $deviceName,
        DevicePlatform $platform,
    ): array {
        $user = User::where('email', $email)->first();

        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            // Generic mesaj + alan-dışı 'auth' anahtarı: hangi alanın yanlış
            // olduğu sızdırılmaz ve mesaj e-posta alanına iliştirilmez.
            throw ValidationException::withMessages([
                'auth' => 'E-posta veya şifre hatalı.',
            ]);
        }

        if (! $user->is_active) {
            abort(Response::HTTP_FORBIDDEN, 'Hesabınız devre dışı bırakılmış.');
        }

        $issued = $this->issueDeviceToken->handle($user, $deviceName, $platform);

        return [
            'user' => $user,
            'token' => $issued['token'],
        ];
    }
}

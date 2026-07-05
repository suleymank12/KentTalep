<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ChangePassword;
use App\Actions\Auth\LoginUser;
use App\Actions\Auth\RegisterUser;
use App\Actions\Auth\RequestPasswordReset;
use App\Actions\Auth\ResetPassword;
use App\Actions\Auth\SyncDevicePushToken;
use App\Enums\DevicePlatform;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateDeviceRequest;
use App\Http\Resources\UserDeviceResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $action): JsonResponse
    {
        $result = $action->handle(
            $request->string('name')->value(),
            $request->string('email')->value(),
            $request->filled('phone') ? $request->string('phone')->value() : null,
            $request->string('password')->value(),
            $request->string('device_name')->value(),
            DevicePlatform::from($request->string('platform')->value()),
        );

        return response()->json([
            'token' => $result['token'],
            'user' => new UserResource($result['user']->load('roles')),
        ], Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request, LoginUser $action): JsonResponse
    {
        $result = $action->handle(
            $request->string('email')->value(),
            $request->string('password')->value(),
            $request->string('device_name')->value(),
            DevicePlatform::from($request->string('platform')->value()),
        );

        return response()->json([
            'token' => $result['token'],
            'user' => new UserResource($result['user']->load('roles')),
        ]);
    }

    public function logout(Request $request): Response
    {
        $this->currentToken($request)->delete();

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($this->authUser($request)->load('roles'));
    }

    public function updateDevice(UpdateDeviceRequest $request, SyncDevicePushToken $action): UserDeviceResource
    {
        $device = $action->handle(
            $this->authUser($request),
            $this->currentToken($request),
            $request->string('push_token')->value(),
        );

        return new UserDeviceResource($device);
    }

    public function changePassword(ChangePasswordRequest $request, ChangePassword $action): Response
    {
        $action->handle(
            $this->authUser($request),
            $this->currentToken($request),
            $request->string('current_password')->value(),
            $request->string('password')->value(),
        );

        return response()->noContent();
    }

    public function forgotPassword(ForgotPasswordRequest $request, RequestPasswordReset $action): JsonResponse
    {
        $action->handle($request->string('email')->value());

        return response()->json([
            'message' => 'E-posta kayıtlıysa sıfırlama kodu gönderildi.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPassword $action): JsonResponse
    {
        $action->handle(
            $request->string('email')->value(),
            $request->string('code')->value(),
            $request->string('password')->value(),
        );

        return response()->json([
            'message' => 'Şifreniz güncellendi. Lütfen tekrar giriş yapın.',
        ]);
    }

    /**
     * Mevcut isteğin Sanctum kişisel erişim token'ını döndürür.
     * auth:sanctum arkasındaki rotalarda her zaman gerçek bir token mevcuttur.
     */
    private function currentToken(Request $request): PersonalAccessToken
    {
        return $this->authUser($request)->currentAccessToken();
    }
}

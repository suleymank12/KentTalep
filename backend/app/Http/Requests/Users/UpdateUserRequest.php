<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\Role;
use App\Models\User;
use App\Support\LastAdminGuard;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'role' => ['sometimes', 'required', Rule::enum(Role::class)],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $target = $this->route('user');
            $actor = $this->user();

            if (! $target instanceof User) {
                return;
            }

            if ($actor instanceof User && $actor->is($target)) {
                if ($this->has('role')) {
                    $v->errors()->add('role', 'Kendi rolünüzü değiştiremezsiniz.');
                }
                if ($this->has('is_active')) {
                    $v->errors()->add('is_active', 'Kendi aktiflik durumunuzu değiştiremezsiniz.');
                }
            }

            if (LastAdminGuard::isLastActiveAdmin($target)) {
                $demoting = $this->has('role')
                    && $this->string('role')->value() !== Role::Admin->value;
                $deactivating = $this->has('is_active') && $this->boolean('is_active') === false;

                if ($demoting || $deactivating) {
                    $v->errors()->add(
                        'user',
                        'Sistemdeki son aktif admin pasifleştirilemez veya rolü düşürülemez.',
                    );
                }
            }
        });
    }
}

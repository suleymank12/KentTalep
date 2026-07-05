<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Models\User;
use App\Support\LastAdminGuard;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DestroyUserRequest extends FormRequest
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
        return [];
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
                $v->errors()->add('user', 'Kendinizi silemezsiniz.');
            }

            if (LastAdminGuard::isLastActiveAdmin($target)) {
                $v->errors()->add('user', 'Sistemdeki son aktif admin silinemez.');
            }
        });
    }
}

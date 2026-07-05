<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Yetki UserPolicy (authorizeResource) tarafından denetlenir.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role' => ['required', Rule::enum(Role::class)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

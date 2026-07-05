<?php

declare(strict_types=1);

namespace App\Http\Requests\Tickets;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
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
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $target = User::find($this->integer('assigned_to'));

            if ($target === null || ! $target->is_active || ! $target->hasRole(Role::Staff->value)) {
                $v->errors()->add('assigned_to', 'Seçilen kullanıcı aktif bir personel değil.');
            }
        });
    }
}

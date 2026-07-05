<?php

declare(strict_types=1);

namespace App\Http\Requests\Tickets;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Süper küme doğrulama; hangi alanı kimin değiştirebileceği ve durum
     * kısıtları UpdateTicket action'ında rol/duruma göre uygulanır.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:150'],
            'description' => ['sometimes', 'required', 'string', 'min:10', 'max:5000'],
            'category_id' => ['sometimes', 'required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'priority' => ['sometimes', 'required', Rule::enum(TicketPriority::class)],
        ];
    }
}

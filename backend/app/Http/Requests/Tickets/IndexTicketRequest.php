<?php

declare(strict_types=1);

namespace App\Http\Requests\Tickets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTicketRequest extends FormRequest
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
            'status' => ['sometimes', Rule::enum(TicketStatus::class)],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'priority' => ['sometimes', Rule::enum(TicketPriority::class)],
            'q' => ['sometimes', 'string', 'max:150'],
            'near' => ['sometimes', 'string', 'regex:/^-?\d+(\.\d+)?,\s*-?\d+(\.\d+)?$/'],
            'radius_km' => ['sometimes', 'required_with:near', 'numeric', 'between:0.1,50'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

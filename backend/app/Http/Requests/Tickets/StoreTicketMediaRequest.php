<?php

declare(strict_types=1);

namespace App\Http\Requests\Tickets;

use App\Enums\TicketMediaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Gerçek MIME/boyut/piksel denetimleri TicketMediaProcessor'da yapılır;
     * burada yalnız dosya varlığı ve tip doğrulanır ('image' değil 'file' —
     * uzantı aldatmacası işlemcinin finfo kontrolüne ulaşsın).
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
            'type' => ['required', Rule::enum(TicketMediaType::class)],
        ];
    }
}

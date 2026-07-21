<?php

declare(strict_types=1);

namespace App\Http\Requests\Tickets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Closure;
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
            // Tek değer ya da virgüllü liste (ör. "pending,assigned"); her parça
            // TicketStatus enum'ında doğrulanır, geçersiz değer 422 verir.
            'status' => ['sometimes', 'string', $this->statusListRule()],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'priority' => ['sometimes', Rule::enum(TicketPriority::class)],
            'q' => ['sometimes', 'string', 'max:150'],
            'near' => ['sometimes', 'string', 'regex:/^-?\d+(\.\d+)?,\s*-?\d+(\.\d+)?$/'],
            'radius_km' => ['sometimes', 'required_with:near', 'numeric', 'between:0.1,50'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Virgülle ayrılmış status listesinin her parçasını enum'a karşı doğrular.
     */
    private function statusListRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $statuses = array_filter(array_map('trim', explode(',', (string) $value)), fn (string $s): bool => $s !== '');

            if ($statuses === []) {
                $fail('Durum boş olamaz.');

                return;
            }

            foreach ($statuses as $status) {
                if (TicketStatus::tryFrom($status) === null) {
                    $fail("Geçersiz durum değeri: {$status}.");
                }
            }
        };
    }
}

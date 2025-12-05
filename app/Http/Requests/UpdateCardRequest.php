<?php

namespace App\Http\Requests;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $cardId = $this->route('card')->id;

        return [
            'number' => ['sometimes', 'string', 'min:15', 'max:16',
                Rule::unique('cards', 'number')->ignore($cardId),
            ],
            'status' => ['sometimes', new Enum(CardStatusEnum::class)],
            'brand' => ['sometimes', new Enum(CardBrandEnum::class)],
            'balance' => 'prohibited',
            'user_id' => 'prohibited',
            'created_at' => 'prohibited',
        ];
    }
}

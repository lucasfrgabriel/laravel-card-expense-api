<?php

namespace App\Http\Requests\Cards;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $card = $this->route('card');
        return Gate::forUser($this->user())->allows('update', $card);
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
        ];
    }

    public function getNumber(): string|null
    {
        return $this->input('number');
    }

    public function getStatus(): CardStatusEnum|null
    {
        $statusValue = $this->input('status');

        if ($statusValue === null) {
            return null;
        }

        return CardStatusEnum::from($statusValue);
    }

    public function getBrand(): CardBrandEnum|null
    {
        $brandValue = $this->input('brand');

        if ($brandValue === null) {
            return null;
        }

        return CardBrandEnum::from($brandValue);
    }
}

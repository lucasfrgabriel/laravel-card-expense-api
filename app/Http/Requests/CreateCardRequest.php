<?php

namespace App\Http\Requests;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateCardRequest extends FormRequest
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
        return [
            'number' => 'required|string|min:15|max:16|unique:cards,number',
            'status' => ['required', new Enum(CardStatusEnum::class)],
            'brand' => ['sometimes', new Enum(CardBrandEnum::class)],
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}

<?php

namespace App\Http\Requests\Cards;

use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ViewAllCardsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::forUser($this->user())->allows('viewAny', Card::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'paginate' => 'sometimes|nullable|integer|min:1',
        ];
    }

    public function getPaginate(): int
    {
        return $this->query('paginate', 2);
    }
}

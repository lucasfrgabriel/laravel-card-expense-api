<?php

namespace App\Http\Requests\Expenses;

use App\Models\Expense;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CreateExpenseRequest extends FormRequest
{

    public function __construct(protected UserRepository $userRepository){}

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $card_id = $this->input('card_id');
        if(!$card_id || $card_id < 1) return true;

        $cardOwner = $this->userRepository->findUserByCardId($card_id);
        if(!$cardOwner) return true;

        return Gate::forUser($this->user())->allows('createFor', [Expense::class, $cardOwner]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'card_id' => 'required|integer|exists:cards,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
        ];
    }

    public function getCardId(): int
    {
        return $this->get('card_id');
    }

    public function getAmount(): float
    {
        return $this->get('amount');
    }

    public function getDescription(): string
    {
        return $this->get('description');
    }
}

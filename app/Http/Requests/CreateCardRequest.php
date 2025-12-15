<?php

namespace App\Http\Requests;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use App\Models\Card;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class CreateCardRequest extends FormRequest
{

    public function __construct(protected UserRepository $userRepository){}

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user_id = $this->input('user_id');
        if(!$user_id) return true;

        $cardOwner = $this->userRepository->findUserById($user_id);
        return Gate::forUser($this->user())->allows('createFor', [Card::class, $cardOwner]);
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

    public function returnData(): array
    {
        $validated = $this->validated();
        return array_merge($validated, ['expenses' => []], ['balance' => 0]);
    }
}

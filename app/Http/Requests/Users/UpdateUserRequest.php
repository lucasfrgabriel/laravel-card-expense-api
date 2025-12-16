<?php

namespace App\Http\Requests\Users;

use App\Enums\UserTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        return Gate::forUser($this->user())->allows('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => 'sometimes|string|min:8',
            'type' => ['sometimes', new Enum(UserTypeEnum::class)],
        ];
    }

    public function getName(): string|null
    {
        return $this->input('name');
    }

    public function getEmail(): string|null
    {
        return $this->input('email');
    }

    public function getPassword(): string|null
    {
        return $this->input('password');
    }

    public function getType(): UserTypeEnum|null
    {
        $userType = $this->input('type');

        if($userType === null){
            return null;
        }

        return UserTypeEnum::from($userType);
    }
}

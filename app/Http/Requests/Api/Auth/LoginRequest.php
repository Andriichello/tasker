<?php

namespace App\Http\Requests\Api\Auth;

use App\Enum\TaskStatus;
use App\Enum\TaskVisibility;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\Crud\StoreRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class LoginRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                Rule::exists(User::class, 'email'),
            ],
            'password' => [
                'required',
                'string',
                'min:1',
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="LoginRequest",
     *   description="Request for logging in with email and password.",
     *   required={"email", "password"},
     *   @OA\Property(property="email", type="string", example="example@example.com"),
     *   @OA\Property(property="password", type="string", example="secret"),
     * )
     */
}

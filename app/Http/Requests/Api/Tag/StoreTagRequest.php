<?php

namespace App\Http\Requests\Api\Tag;

use App\Http\Requests\Crud\StoreRequest;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class StoreTagRequest extends StoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $isLoggedIn = $this->user() instanceof User;

        if (!$isLoggedIn) {
            $this->message = 'Log in to be able to create tags.';
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:25',
                Rule::unique(Tag::class, 'name'),
            ],
            'description' => [
                'sometimes',
                'nullable',
                'min:1',
                'max:100',
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="StoreTagRequest",
     *   description="Request for creating a tag record.",
     *   required={"name"},
     *   @OA\Property(property="name", type="string", example="Important", description="Tag name"),
     *   @OA\Property(property="description", type="string", example="Tasks that need immediate attention",
     *     description="Tag description", nullable=true)
     * )
     */
}

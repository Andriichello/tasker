<?php

namespace App\Http\Requests\Api\Task;

use App\Enum\TaskStatus;
use App\Enum\TaskVisibility;
use App\Http\Requests\Crud\StoreRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class StoreTaskRequest extends StoreRequest
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
            $this->message = 'Log in to be able to create tasks.';
            return false;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(TaskStatus::cases()),
            ],
            'visibility' => [
                'required',
                'string',
                Rule::in(TaskVisibility::cases()),
            ],
            'title' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'description' => [
                'sometimes',
                'nullable',
                'min:1',
                'max:4160',
            ],
            'tags' => [
                'sometimes',
                'array',
            ],
            'tags.*' => [
                'string',
                'min:1',
                'max:25',
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="StoreTaskRequest",
     *   description="Request for creating a task record.",
     *   required={"status", "visibility", "title"},
     *   @OA\Property(property="status", type="string", example="to-do", description="Task status",
     *     enum={"to-do", "in-progress", "done", "canceled"}),
     *   @OA\Property(property="visibility", type="string", example="public", description="Task visibility",
     *     enum={"public", "private"}),
     *   @OA\Property(property="title", type="string", example="Complete project documentation", description="Task title"),
     *   @OA\Property(property="description", type="string", example="Write comprehensive documentation for the project",
     *     description="Task description", nullable=true),
     *   @OA\Property(property="tags", type="array", description="List of ALL the tag names that the task will have",
     *     @OA\Items(type="string", example="important"))
     * )
     */
}

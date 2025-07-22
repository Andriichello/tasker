<?php

namespace App\Http\Requests\Api\Task;

use App\Enum\TaskStatus;
use App\Enum\TaskVisibility;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class UpdateTaskRequest extends UpdateRequest
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
            $this->message = 'Log in to be able to update tasks.';
            return false;
        }

        $owns = Task::query()
            ->where('user_id', $this->user()->id)
            ->whereKey($this->route('id'))
            ->exists();

        if (!$owns) {
            $this->message = 'You can only delete your own tasks.';
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
            'status' => [
                'sometimes',
                'string',
                Rule::in(TaskStatus::cases()),
            ],
            'visibility' => [
                'sometimes',
                'string',
                Rule::in(TaskVisibility::cases()),
            ],
            'title' => [
                'sometimes',
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
                'max:255',
            ],
        ];
    }

    /**
     * @OA\Schema(
     *   schema="UpdateTaskRequest",
     *   description="Request for updating a task record.",
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

<?php

namespace App\Http\Requests\Api\Task;

use App\Enum\TaskStatus;
use App\Enum\TaskVisibility;
use App\Http\Requests\Crud\DestroyRequest;
use App\Http\Requests\Crud\StoreRequest;
use App\Http\Requests\Crud\UpdateRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

class DestroyTaskRequest extends DestroyRequest
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
            $this->message = 'Log in to be able to delete tasks.';
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
        ];
    }
}

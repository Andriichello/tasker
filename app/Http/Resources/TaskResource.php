<?php

namespace App\Http\Resources;

use App\Enum\TaskStatus;
use App\Enum\TaskVisibility;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @mixin Task
 */
class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status->value,
            'title' => $this->title,
            'description' => $this->description,
            'visibility' => $this->visibility->value,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            // tags collection can be preloaded to avoid N+1 queries
            'tags' => $this->tags->pluck('name')->all(),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Task",
     *   description="Task resource object",
     *   required = {"id", "user_id", "status", "title", "description", "visibility", "created_at", "updated_at"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="user_id", type="integer", example=1),
     *   @OA\Property(property="status", type="string", example="pending"),
     *   @OA\Property(property="title", type="string", example="Complete project documentation"),
     *   @OA\Property(property="description", type="string", example="Write comprehensive documentation for the project", nullable=true),
     *   @OA\Property(property="visibility", type="string", example="public"),
     *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     * )
     */
}

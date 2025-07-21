<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'status' => $this->status,
            'title' => $this->title,
            'description' => $this->description,
            'visibility' => $this->visibility,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Task",
     *   description="Task resource object",
     *   required = {"id", "user_id", "status", "title", "description", "visibility", "created_at", "updated_at"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="user_id", type="integer", example=1),
     *   @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed"}, example="pending"),
     *   @OA\Property(property="title", type="string", example="A short title."),
     *   @OA\Property(property="description", type="string", nullable=true, example="Some extensive description."),
     *   @OA\Property(property="visibility", type="string", enum={"public", "private"}, example="private"),
     *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     * )
     */
}

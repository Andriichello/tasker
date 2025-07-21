<?php

namespace App\Http\Resources;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @mixin Tag
 */
class TagResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Tag",
     *   description="Tag resource object",
     *   required = {"id", "name", "description"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="name", type="string", example="Important"),
     *   @OA\Property(property="description", type="string",
     *     example="Tasks that need immediate attention", nullable=true),
     * )
     */
}

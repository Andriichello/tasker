<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @mixin User
 */
class MeResource extends UserResource
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
            ...parent::toArray($request),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @OA\Schema(
     *   schema="Me",
     *   description="Me (User) resource object",
     *   required = {"id", "name", "email", "email_verified_at", "created_at", "updated_at"},
     *   @OA\Property(property="id", type="integer", example=1),
     *   @OA\Property(property="name", type="string", example="Admin Admins"),
     *   @OA\Property(property="email", type="string", example="admin@email.com", nullable=true),
     *   @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
     *   @OA\Property(property="updated_at", type="string", format="date-time", nullable=true),
     * )
     */
}

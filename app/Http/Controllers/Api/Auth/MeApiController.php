<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\MeResource;
use App\Http\Responses\ApiResponse;
use App\Models\AccessToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

class MeApiController extends ResourceController
{
    /**
     * Controller's resource class.
     *
     * @var class-string<JsonResource>
     */
    protected string $resourceClass = MeResource::class;

    /**
     * Logs user in with given credentials (email and password).
     *
     * @param LoginRequest $request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse([
            'data' => new MeResource($request->user()),
            'message' => 'OK',
        ]);
    }

    /**
     * @OA\Get  (
     *   path="/api/me",
     *   summary="Get currently logged-in user.",
     *   operationId="me",
     *   security={{"bearerAuth": {}}},
     *   tags={"auth"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Currently logged-in user has been returned.",
     *     @OA\JsonContent(ref ="#/components/schemas/MeResponse")
     *  ),
     * )
     *
     * @OA\Schema(
     *   schema="MeResponse",
     *   description="Currently logged-in user response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref ="#/components/schemas/Me"),
     *   @OA\Property(property="message", type="string", example="OK")
     * )
     */
}

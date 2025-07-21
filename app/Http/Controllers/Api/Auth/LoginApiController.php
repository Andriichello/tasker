<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\MeResource;
use App\Http\Responses\ApiResponse;
use App\Models\AccessToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class LoginApiController extends ResourceController
{
    /**
     * Controller's resource class.
     *
     * @var class-string<JsonResource>
     */
    protected string $resourceClass = MeResource::class;

    /**
     * Attempts to log user in by given credentials.
     *
     * @param array $credentials
     * @param bool $remember
     *
     * @return User|null
     * @SuppressWarnings(PHPMD)
     */
    public function attempt(array $credentials, bool $remember = false): ?User
    {
        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        if ($guard->attempt($credentials, $remember)) {
            /** @var User $user */
            $user = $guard->user();
        }

        return $user ?? null;
    }

    /**
     * Logs user in with given credentials (email and password).
     *
     * @param LoginRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = $this->attempt(Arr::only($request->validated(), ['email', 'password']));

        if ($user instanceof User) {
            $token = $user->createToken($request->userAgent() ?? 'Unknown');

            return new JsonResponse([
                'data' => [
                    'token_type' => 'Bearer',
                    'token' => $token->plainTextToken,
                    'user' => new MeResource($user),
                ],
            ]);
        }

        throw new AuthenticationException('Failed to log in.');
    }

    /**
     * @OA\Post(
     *   path="/api/login",
     *   operationId="login",
     *   summary="Log in using email and password",
     *   tags={"auth"},
     *
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref = "#/components/schemas/LoginRequest")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Logged in successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="data", type="object",
     *         @OA\Property(property="token_type", type="string", example="Bearer"),
     *         @OA\Property(property="token", type="string", example="eyJ0eXAiOiJ..."),
     *         @OA\Property(property="user", ref = "#/components/schemas/Me")
     *       )
     *     )
     *   ),
     *
     *   @OA\Response(
     *     response=401,
     *     description="Invalid credentials",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Failed to log in.")
     *     )
     *   )
     * )
     */
}

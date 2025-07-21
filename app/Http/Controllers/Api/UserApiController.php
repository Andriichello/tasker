<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Crud\IndexRequest;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Resources\UserResource;
use App\Queries\UserQuery;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @property UserRepository $repository
 *
 * @method UserQuery builder(Request $request)
 */
class UserApiController extends CrudController
{
    /**
     * Controller's resource class.
     *
     * @var class-string<JsonResource>
     */
    protected string $resourceClass = UserResource::class;

    /**
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Returns a list of user records.
     *
     * @param IndexRequest $request
     *
     * @return JsonResponse
     */
    public function index(IndexRequest $request): JsonResponse
    {
        return $this->asResourceCollectionResponse(
            // index query conditions are already applied here
            $this->builder($request)->get()
        );
    }

    /**
     * Returns a user record by id.
     *
     * @param ShowRequest $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(ShowRequest $request, int $id): JsonResponse
    {
        return $this->asResourceResponse(
            // index query conditions are already applied here
            $this->builder($request)
                ->findOrFail($id)
        );
    }

    /**
     * @OA\Get(
     *   path="/api/users",
     *   summary="Index users.",
     *   operationId="indexUsers",
     *   tags={"users"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index users response object.",
     *     @OA\JsonContent(ref="#/components/schemas/IndexUsersResponse")
     *   ),
     * )
     *
     * @OA\Get(
     *   path="/api/users/{id}",
     *   summary="Show user by id.",
     *   operationId="showUser",
     *   tags={"users"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the user."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show user response object.",
     *     @OA\JsonContent(ref="#/components/schemas/ShowUserResponse")
     *   ),
     * )
     */

    /**
     * @OA\Schema(
     *   schema="IndexUsersResponse",
     *   description="Index users response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     *
     * @OA\Schema(
     *   schema="ShowUserResponse",
     *   description="Show user response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/User"),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     */
}

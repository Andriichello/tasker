<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Api\Tag\DestroyTagRequest;
use App\Http\Requests\Api\Tag\StoreTagRequest;
use App\Http\Requests\Api\Tag\UpdateTagRequest;
use App\Http\Requests\Crud\IndexRequest;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Resources\TagResource;
use App\Models\User;
use App\Queries\TagQuery;
use App\Repositories\TagRepository;
use HttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @property TagRepository $repository
 *
 * @method TagQuery builder(Request $request)
 */
class TagApiController extends CrudController
{
    /**
     * Controller's resource class.
     *
     * @var class-string<JsonResource>
     */
    protected string $resourceClass = TagResource::class;

    /**
     * @param TagRepository $repository
     */
    public function __construct(TagRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Returns a list of tag records.
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
     * Returns a tag record by id.
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
     * Stores a tag record in the database.
     *
     * @param StoreTagRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreTagRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $tag = $this->repository->create($attributes);

        return $this->asResourceResponse($tag);
    }

    /**
     * Updates a tag record in the database by id.
     *
     * @param UpdateTagRequest $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update(UpdateTagRequest $request, int $id): JsonResponse
    {
        // index query conditions are already applied here
        $tag = $this->builder($request)
            ->findOrFail($id);

        $attributes = $request->validated();
        $this->repository->update($tag, $attributes);

        return $this->asResourceResponse($tag);
    }

    /**
     * Updates a tag record in the database by id.
     *
     * @param DestroyTagRequest $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws HttpException
     */
    public function destroy(DestroyTagRequest $request, int $id): JsonResponse
    {
        // index query conditions are already applied here
        $tag = $this->builder($request)
            ->findOrFail($id);

        if (!$this->repository->delete($tag)) {
            throw new HttpException('Failed to delete the tag.');
        }

        return new JsonResponse(['message' => 'Deleted']);
    }

    /**
     * @OA\Get(
     *   path="/api/tags",
     *   summary="Index tags.",
     *   operationId="indexTags",
     *   tags={"tags"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index tags response object.",
     *     @OA\JsonContent(ref="#/components/schemas/IndexTagsResponse")
     *   ),
     * )
     *
     * @OA\Get(
     *   path="/api/tags/{id}",
     *   summary="Show tag by id.",
     *   operationId="showTag",
     *   tags={"tags"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the tag."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show tag response object.",
     *     @OA\JsonContent(ref="#/components/schemas/ShowTagResponse")
     *   ),
     * )
     *
     * @OA\Post(
     *   path="/api/tags",
     *   summary="Store a tag.",
     *   operationId="storeTag",
     *   security={{"bearerAuth": {}}},
     *   tags={"tags"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store tag request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreTagRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Store tag response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreTagResponse")
     *   ),
     * )
     *
     * @OA\Patch(
     *   path="/api/tags/{id}",
     *   summary="Update a tag.",
     *   operationId="updateTag",
     *   security={{"bearerAuth": {}}},
     *   tags={"tags"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *   description="Id of the tag."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update tag request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateTagRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update tag response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateTagResponse")
     *   ),
     * )
     *
     * @OA\Delete(
     *   path="/api/tags/{id}",
     *   summary="Delete tag.",
     *   operationId="destroyTag",
     *   security={{"bearerAuth": {}}},
     *   tags={"tags"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the tag."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Delete tag response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyResponse")
     *   ),
     * ),
     */

    /**
     * @OA\Schema(
     *   schema="IndexTagsResponse",
     *   description="Index tags response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Tag")),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     *
     * @OA\Schema(
     *   schema="ShowTagResponse",
     *   description="Show tag response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/Tag"),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     *
     * @OA\Schema(
     *   schema="StoreTagResponse",
     *   description="Store tag response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/Tag"),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     *
     * @OA\Schema(
     *   schema="UpdateTagResponse",
     *   description="Update tag response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/Tag"),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     */
}

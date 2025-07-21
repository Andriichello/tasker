<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Crud\IndexRequest;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Resources\TaskResource;
use App\Queries\TaskQuery;
use App\Repositories\TaskRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @property TaskRepository $repository
 *
 * @method TaskQuery builder(Request $request)
 */
class TaskApiController extends CrudController
{
    /**
     * Controller's resource class.
     *
     * @var class-string<JsonResource>
     */
    protected string $resourceClass = TaskResource::class;

    /**
     * @param TaskRepository $repository
     */
    public function __construct(TaskRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Returns a list of task records.
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
     * Returns a task record by id.
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
     *   path="/api/tasks",
     *   summary="Index tasks.",
     *   operationId="indexTasks",
     *   tags={"tasks"},
     *
     *   @OA\Response(
     *     response=200,
     *     description="Index tasks response object.",
     *     @OA\JsonContent(ref="#/components/schemas/IndexTasksResponse")
     *   ),
     * )
     *
     * @OA\Get(
     *   path="/api/tasks/{id}",
     *   summary="Show task by id.",
     *   operationId="showTask",
     *   tags={"tasks"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the task."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Show task response object.",
     *     @OA\JsonContent(ref="#/components/schemas/ShowTaskResponse")
     *   ),
     * )
     */

    /**
     * @OA\Schema(
     *   schema="IndexTasksResponse",
     *   description="Index tasks response object.",
     *   required = {"data", "meta", "message"},
     *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     *
     * @OA\Schema(
     *   schema="ShowTaskResponse",
     *   description="Show task response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/Task"),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     */
}

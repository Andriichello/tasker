<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\CrudController;
use App\Http\Requests\Api\Task\DestroyTaskRequest;
use App\Http\Requests\Api\Task\StoreTaskRequest;
use App\Http\Requests\Api\Task\UpdateTaskRequest;
use App\Http\Requests\Crud\IndexRequest;
use App\Http\Requests\Crud\ShowRequest;
use App\Http\Resources\TaskResource;
use App\Models\User;
use App\Queries\TaskQuery;
use App\Repositories\TaskRepository;
use HttpException;
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
     * Stores a task record in the database.
     *
     * @param StoreTaskRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $attributes = $request->validated();

        // Create the task with all attributes including tags
        // The repository will handle tag synchronization internally
        $task = $this->repository->create([
            ...$attributes,
            // assign the task to the user that creates it
            'user_id' => $user->id,
        ]);

        return $this->asResourceResponse($task);
    }

    /**
     * Updates a task record in the database by id.
     *
     * @param UpdateTaskRequest $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update(UpdateTaskRequest $request, int $id): JsonResponse
    {
        // index query conditions are already applied here
        $task = $this->builder($request)
            ->findOrFail($id);

        $attributes = $request->validated();

        // Update the task with all attributes including tags
        // The repository will handle tag synchronization internally
        // If tags are provided, they will be attached/detached as needed
        $this->repository->update($task, $attributes);

        return $this->asResourceResponse($task);
    }

    /**
     * Updates a task record in the database by id.
     *
     * @param DestroyTaskRequest $request
     * @param int $id
     *
     * @return JsonResponse
     * @throws HttpException
     */
    public function destroy(DestroyTaskRequest $request, int $id): JsonResponse
    {
        // index query conditions are already applied here
        $task = $this->builder($request)
            ->findOrFail($id);

        if (!$this->repository->delete($task)) {
            throw new HttpException('Failed to delete the task.');
        }

        return new JsonResponse(['message' => 'Deleted']);
    }

    /**
     * @OA\Get(
     *   path="/api/tasks",
     *   summary="Index tasks.",
     *   operationId="indexTasks",
     *   security={{"bearerAuth": {}}},
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
     *   security={{"bearerAuth": {}}},
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
     *
     * @OA\Post(
     *   path="/api/tasks",
     *   summary="Store a task.",
     *   operationId="storeTask",
     *   security={{"bearerAuth": {}}},
     *   tags={"tasks"},
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Store task request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreTaskRequest")
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Store task response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/StoreTaskResponse")
     *   ),
     * )
     *
     * @OA\Patch(
     *   path="/api/tasks/{id}",
     *   summary="Update a task.",
     *   operationId="updateTask",
     *   security={{"bearerAuth": {}}},
     *   tags={"tasks"},
     *
     *   @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *   description="Id of the task."),
     *
     *  @OA\RequestBody(
     *     required=true,
     *     description="Update task request object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateTaskRequest")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Update task response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/UpdateTaskResponse")
     *   ),
     * )
     *
     * @OA\Delete(
     *   path="/api/tasks/{id}",
     *   summary="Delete task.",
     *   operationId="destroyTask",
     *   security={{"bearerAuth": {}}},
     *   tags={"tasks"},
     *
     *  @OA\Parameter(name="id", required=true, in="path", example=1, @OA\Schema(type="integer"),
     *     description="Id of the task."),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Delete task response object.",
     *     @OA\JsonContent(ref ="#/components/schemas/DestroyResponse")
     *   ),
     * ),
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
     *
     * @OA\Schema(
     *   schema="StoreTaskResponse",
     *   description="Store task response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/Task"),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     *
      * @OA\Schema(
     *   schema="UpdateTaskResponse",
     *   description="Update task response object.",
     *   required = {"data", "message"},
     *   @OA\Property(property="data", ref="#/components/schemas/Task"),
     *   @OA\Property(property="message", type="string", example="OK"),
     * )
     */
}

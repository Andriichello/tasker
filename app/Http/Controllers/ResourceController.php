<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

abstract class ResourceController extends BaseController
{
    /**
     * Controller's resource class.
     *
     * @var class-string<JsonResource>
     */
    protected string $resourceClass;

    /**
     * Get controller's resource class.
     *
     * @return class-string<JsonResource>
     */
    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    /**
     * Returns a given model as a controller's resource instance.
     *
     * @param Model $model
     *
     * @return JsonResource
     */
    public function asResource(Model $model): JsonResource
    {
        $class = $this->getResourceClass();
        return new $class($model);
    }

    /**
     * Return a given collection of models as a controller's
     * resource collection.
     *
     * @param Collection $models
     *
     * @return ResourceCollection
     */
    public function asResourceCollection(Collection $models): ResourceCollection
    {
        $class = $this->getResourceClass();
        return $class::collection($models);
    }

    /**
     * Returns a given model as controller's resource JSON response.
     *
     * @param Model $model
     *
     * @return JsonResponse
     */
    public function asResourceResponse(Model $model): JsonResponse
    {
        return new JsonResponse([
            'data' => $this->asResource($model),
            'message' => 'OK',
        ]);
    }

    /**
     * Returns a given collection as controller's resource collection JSON response.
     *
     * @param Collection $models
     *
     * @return JsonResponse
     */
    public function asResourceCollectionResponse(Collection $models): JsonResponse
    {
        return new JsonResponse([
            'data' => $this->asResourceCollection($models),
            'message' => 'OK',
        ]);
    }
}

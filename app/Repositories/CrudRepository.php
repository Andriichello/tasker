<?php

namespace App\Repositories;

use App\Queries\BaseQuery;
use App\Repositories\Interfaces\CrudRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class CrudRepository extends BaseRepository implements
    CrudRepositoryInterface
{
    /**
     * Get a query builder for the repository's model.
     * `Important: Will apply index query conditions.`
     *
     * @param Request|null $request
     *
     * @return BaseQuery
     * @throws Exception
     */
    public function builder(?Request $request = null): BaseQuery
    {
        return parent::builder()
            ->when($request, fn(BaseQuery $query) => $query->index($request));
    }

    /**
     * Find the model by the id.
     *
     * @param mixed $id
     *
     * @return Model|null
     */
    public function find(mixed $id): ?Model
    {
        return $this->builder()->find($id);
    }

    /**
     * Find the model by the id or throw an exception.
     *
     * @param mixed $id
     *
     * @return Model|null
     */
    public function findOrFail(mixed $id): ?Model
    {
        return $this->builder()->findOrFail($id);
    }

    /**
     * Create a model with given attributes.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes): Model
    {
        return $this->builder()->create($attributes);
    }

    /**
     * Update a model with given attributes.
     *
     * @param Model $model
     * @param array $attributes
     *
     * @return bool
     */
    public function update(Model $model, array $attributes): bool
    {
        return $model->update($attributes);
    }

    /**
     * Delete a given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }
}

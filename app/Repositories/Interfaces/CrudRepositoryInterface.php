<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface CrudRepositoryInterface
{
    /**
     * Find the model by the id.
     *
     * @param mixed $id
     *
     * @return Model|null
     */
    public function find(mixed $id): ?Model;

    /**
     * Find the model by the id or throw an exception.
     *
     * @param mixed $id
     *
     * @return Model|null
     */
    public function findOrFail(mixed $id): ?Model;

    /**
     * Create a model with given attributes.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Update a model with given attributes.
     *
     * @param Model $model
     * @param array $attributes
     *
     * @return bool
     */
    public function update(Model $model, array $attributes): bool;

    /**
     * Delete a given model.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function delete(Model $model): bool;
}

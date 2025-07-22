<?php

namespace App\Repositories;

use App\Queries\BaseQuery;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * Repository's model class.
     *
     * @var class-string<Model>
     */
    protected string $model = Model::class;

    /**
     * Get a repository's model class.
     *
     * @return class-string<Model>
     */
    public function model(): string
    {
        return $this->model;
    }

    /**
     * Get new instance of repository's model class.
     *
     * @return Model|null
     */
    public function instance(): ?Model
    {
        $class = $this->model;
        return new $class();
    }

    /**
     * Get a query builder for the repository's model.
     *
     * @return BaseQuery
     */
    public function builder(): BaseQuery
    {
        /** @var BaseQuery $query */
        $query = ($this->model)::query();
        return $query;
    }
}

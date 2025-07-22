<?php

namespace App\Repositories\Interfaces;

use App\Queries\BaseQuery;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get a repository's model class.
     *
     * @return class-string<Model>
     */
    public function model(): string;

    /**
     * Get new instance of repository's model class.
     *
     * @return Model|null
     */
    public function instance(): ?Model;

    /**
     * Get a query builder for the repository's model.
     *
     * @return BaseQuery
     */
    public function builder(): BaseQuery;
}

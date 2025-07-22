<?php

namespace App\Http\Controllers;

use App\Queries\BaseQuery;
use App\Repositories\CrudRepository;
use Illuminate\Http\Request;

abstract class CrudController extends ResourceController
{
    /**
     * Controller model's CRUD repository.
     *
     * @var CrudRepository
     */
    protected CrudRepository $repository;

    /**
     * CrudController's constructor.
     */
    public function __construct(CrudRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get eloquent query builder instance.
     * `Important: Will apply index query conditions.`
     *
     * @param Request $request
     *
     * @return BaseQuery
     */
    protected function builder(Request $request): BaseQuery
    {
        return $this->repository->builder($request);
    }
}

<?php

namespace App\Queries;

use App\Queries\Interfaces\IndexableInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Request;

class BaseQuery extends EloquentBuilder implements IndexableInterface
{
    /**
     * Apply index query conditions.
     *
     * @param Request $request
     *
     * @return BaseQuery
     * @SuppressWarnings(PHPMD)
     */
    public function index(Request $request): BaseQuery
    {
        return $this;
    }
}

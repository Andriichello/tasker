<?php

namespace App\Queries\Interfaces;

use App\Queries\BaseQuery;
use Illuminate\Http\Request;

/**
 * Interface IndexableInterface.
 */
interface IndexableInterface
{
    /**
     * Apply index query conditions.
     *
     * @param Request $request
     *
     * @return BaseQuery
     */
    public function index(Request $request): BaseQuery;
}

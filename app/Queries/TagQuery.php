<?php

namespace App\Queries;

use Illuminate\Http\Request;

class TagQuery extends BaseQuery
{
    /**
     * Apply index query conditions.
     *
     * @param Request $request
     *
     * @return TagQuery
     * @SuppressWarnings(PHPMD)
     */
    public function index(Request $request): TagQuery
    {
        // there is no restriction on querying tags

        return $this;
    }
}

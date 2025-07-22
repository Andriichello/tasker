<?php

namespace App\Queries;

use Illuminate\Http\Request;

class UserQuery extends BaseQuery
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
        // there is no restriction on querying users

        return $this;
    }
}

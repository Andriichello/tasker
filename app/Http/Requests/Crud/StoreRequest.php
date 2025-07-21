<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\CrudRequest;

class StoreRequest extends CrudRequest
{
    /**
     * Ability, which should be checked on the request.
     *
     * @var string|null
     */
    protected ?string $ability = 'create';
}

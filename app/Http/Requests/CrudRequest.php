<?php

namespace App\Http\Requests;

class CrudRequest extends BaseRequest
{
    /**
     * Ability, which should be checked on the request.
     *
     * @var string|null
     */
    protected ?string $ability = null;

    /**
     * Get ability, which should be checked on the request.
     *
     * @return string|null
     */
    public function getAbility(): ?string
    {
        return $this->ability;
    }
}

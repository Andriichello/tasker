<?php

namespace App\Http\Requests\Crud;

use App\Http\Requests\CrudRequest;

class DestroyRequest extends CrudRequest
{
    /**
     * Ability, which should be checked on the request.
     *
     * @var string|null
     */
    protected ?string $ability = 'delete';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'force' => [
                    'boolean',
                ],
            ]
        );
    }

    /**
     * Get force parameter.
     *
     * @return bool
     */
    public function force(): bool
    {
        return (bool) $this->get('force', false);
    }
}

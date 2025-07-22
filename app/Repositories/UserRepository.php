<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserRepository extends CrudRepository
{
    /**
     * Repository's model class.
     *
     * @var class-string<Model>
     */
    protected string $model = User::class;
}

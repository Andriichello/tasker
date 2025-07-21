<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;

class TaskRepository extends CrudRepository
{
    /**
     * Repository's model class.
     *
     * @var class-string<Model>
     */
    protected string $model = Task::class;
}

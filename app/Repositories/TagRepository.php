<?php

namespace App\Repositories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;

class TagRepository extends CrudRepository
{
    /**
     * Repository's model class.
     *
     * @var class-string<Model>
     */
    protected string $model = Tag::class;
}

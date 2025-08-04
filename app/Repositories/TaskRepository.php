<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Models\Task;
use App\Queries\BaseQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TaskRepository extends CrudRepository
{
    /**
     * Repository's model class.
     *
     * @var class-string<Model>
     */
    protected string $model = Task::class;

    public function builder(?Request $request = null): BaseQuery
    {
        $builder = parent::builder($request);

        if ($request) {
            $builder = $builder->index($request);

            $search = $request->query('search');
            if (isset($search) && is_string($search) && strlen($search) > 0) {
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                if (!empty($words)) {
                    $builder->where(function ($query) use ($words) {
                        foreach ($words as $word) {
                            $query->orWhere(function ($subQuery) use ($word) {
                                $subQuery->where('tasks.title', 'like', '%' . $word . '%')
                                    ->orWhere('tasks.description', 'like', '%' . $word . '%');
                            });
                        }
                    });
                }
            }

            $tag = $request->query('tag');
            if (isset($tag) && is_string($tag) && strlen($tag) > 0) {
                $builder->join('tag_task', 'tasks.id', '=', 'tag_task.task_id')
                    ->join('tags', 'tags.id', '=', 'tag_task.tag_id')
                    ->where('tags.name', $tag)
                    ->select('tasks.*');
            }

            $status = $request->query('status');
            if (isset($status) && is_string($status) && strlen($status) > 0)  {
                $builder->where('status', $status);
            }
        }

        return $builder;
    }

    /**
     * Create a task with given attributes and sync tags.
     *
     * @param array $attributes
     *
     * @return Task
     */
    public function create(array $attributes): Task
    {
        $tags = $attributes['tags'] ?? [];
        unset($attributes['tags']);

        /** @var Task $task */
        $task = parent::create($attributes);

        if (!empty($tags)) {
            $this->syncTags($task, $tags);
        }

        return $task;
    }

    /**
     * Update a task with given attributes and sync tags.
     *
     * @param Task $model
     * @param array $attributes
     *
     * @return bool
     */
    public function update(Model $model, array $attributes): bool
    {
        $tags = $attributes['tags'] ?? null;
        unset($attributes['tags']);

        $result = parent::update($model, $attributes);

        if ($tags !== null) {
            $changed = $this->syncTags($model, $tags);

            if ($changed) {
                $model->touch();
            }
        }

        return $result;
    }

    /**
     * Sync tags with the task.
     * If a tag doesn't exist, it will be created.
     *
     * @param Task $task
     * @param array $tagNames
     *
     * @return bool
     */
    protected function syncTags(Task $task, array $tagNames): bool
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            /** @var Tag $tag */
            $tag = Tag::query()
                ->firstOrCreate(['name' => $tagName]);

            $tagIds[] = $tag->id;
        }

        $array = $task->tags()->sync($tagIds);

        return count($array['attached']) || count($array['detached']) || count($array['updated']);
    }
}

<?php

namespace App\Repositories;

use App\Models\Tag;
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
            $this->syncTags($model, $tags);
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
     * @return void
     */
    protected function syncTags(Task $task, array $tagNames): void
    {
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            /** @var Tag $tag */
            $tag = Tag::query()
                ->firstOrCreate(['name' => $tagName]);

            $tagIds[] = $tag->id;
        }

        $task->tags()->sync($tagIds);
    }
}

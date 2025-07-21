<?php

namespace App\Queries;

use App\Enum\TaskVisibility;
use App\Models\User;
use Illuminate\Http\Request;

class TaskQuery extends BaseQuery
{
    /**
     * Apply index query conditions.
     *
     * @param Request $request
     *
     * @return TaskQuery
     * @SuppressWarnings(PHPMD)
     */
    public function index(Request $request): TaskQuery
    {
        $user = $request->user();

        if ($user instanceof User) {
            $this->where(function (TaskQuery $query) use ($user) {
                $query->where('visibility', TaskVisibility::Public)
                    ->orWhere('user_id', $user->id);
            });

            return $this;
        }

        $this->where('visibility', TaskVisibility::Public);

        return $this;
    }
}

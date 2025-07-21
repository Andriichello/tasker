<?php

namespace App\Http\Requests\Api\Tag;

use App\Http\Requests\Crud\DestroyRequest;
use App\Models\Tag;
use App\Models\User;

class DestroyTagRequest extends DestroyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $isLoggedIn = $this->user() instanceof User;

        if (!$isLoggedIn) {
            $this->message = 'Log in to be able to delete tags.';
            return false;
        }

        /** @var Tag|null $tag */
        $tag = Tag::query()
            ->find($this->route('id'));

        if ($tag) {
            $shared = $tag->tasks()
                ->whereNot('user_id', $this->user()->id)
                ->exists();

            if ($shared) {
                $this->message = 'You can only delete tags' .
                    ' that are not used by other users\' tasks.';
                return false;
            }
        }

        return true;
    }
}

<?php

namespace App\Models;

use App\Enum\TaskStatus;
use App\Enum\TaskVisibility;
use App\Queries\TaskQuery;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property TaskStatus $status
 * @property string $title
 * @property string|null $description
 * @property TaskVisibility $visibility
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read Collection|Tag[] $tags
 *
 * @method static TaskQuery query()
 */
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'status',
        'title',
        'description',
        'visibility',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'visibility' => TaskVisibility::class,
        ];
    }

    /**
     * Get the user, who owns the task.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tags associated with the task.
     *
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * @param DatabaseBuilder $query
     *
     * @return TaskQuery
     */
    public function newEloquentBuilder($query): TaskQuery
    {
        return new TaskQuery($query);
    }
}

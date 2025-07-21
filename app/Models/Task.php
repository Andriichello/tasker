<?php

namespace App\Models;

use App\Enum\TaskStatus;
use App\Enum\TaskVisibility;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property User $user
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
}

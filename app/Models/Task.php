<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'assign_user_id',
        'subject',
        'note',
        'status',
        'notify_before_deadline',
        'due_date',
        'submitted_at',
        'assign_by',
        'publish_by',
        'published_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'assign_user_id' => 'integer',
            'subject' => 'string',
            'note' => 'string',
            'status' => TaskStatus::class,
            'notify_before_deadline' => 'boolean',
            'due_date' => 'datetime',
            'submitted_at' => 'datetime',
            'created_by' => 'integer',
            'published_by' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function assignTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_user_id');
    }

    public function assignBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_by');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'task_topic');
    }

    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }
}

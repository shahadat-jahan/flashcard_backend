<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Post extends Model
{
    use HasFactory, LogsActivity, Searchable, SoftDeletes;

    protected $fillable = [
        'author_id',
        'task_id',
        'type',
        'title',
        'slug',
        'content',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'author_id' => 'integer',
            'task_id' => 'integer',
            'type' => PostType::class,
            'title' => 'string',
            'slug' => 'string',
            'content' => 'string',
            'status' => PostStatus::class,
            'created_by' => 'integer',
            'approved_by' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Set the title and automatically generate a unique slug.
     */
    public function setTitleAttribute(string $value): void
    {
        $this->attributes['title'] = $value;

        $existingSlug = $this->exists ? $this->slug : null;
        $this->attributes['slug'] = $this->generateUniqueSlug($value, $existingSlug);
    }

    /**
     * Generate a unique slug for the post.
     */
    private function generateUniqueSlug(string $title, ?string $existingSlug = null): string
    {
        $slug = Str::slug($title);

        $query = self::where('slug', 'LIKE', "{$slug}%");
        if ($existingSlug) {
            $query->where('slug', '!=', $existingSlug);
        }

        $latestSlug = $query->orderBy('id', 'desc')->value('slug');
        if ($latestSlug) {
            $number = intval(str_replace("{$slug}-", '', $latestSlug));
            $slug = "{$slug}-".($number + 1);
        }

        return $slug;
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'post_topic');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function decline(): HasMany
    {
        return $this->hasMany(PostDeclineReason::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes', 'post_id', 'user_id')->withTimestamps();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'users.first_name' => '',
            'users.last_name' => '',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($model) {
            // Skip setting `created_by` during seeding
            if (! app()->runningInConsole() || ! defined('IS_SEEDING') || ! IS_SEEDING) {
                $model->created_by = auth()->id();
            }
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}

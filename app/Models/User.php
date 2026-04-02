<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, LogsActivity, Notifiable, Searchable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'email_verified_at',
        'status',
        'role',
        'avatar',
        'designation_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Appends name attribute
     */
    protected $appends = ['name'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'role' => UserRole::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['remember_token'])
            ->dontLogIfAttributesChangedOnly(['remember_token'])
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }

    public function hasPassword(): bool
    {
        return $this->password !== null;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'user_topic');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function mentionedInComments()
    {
        return $this->belongsToMany(Comment::class, 'comment_user_mention', 'mentioned_user_id', 'comment_id')
            ->withTimestamps();
    }

    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'likes', 'user_id', 'post_id')
            ->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'follows', 'follower_user_id', 'following_user_id')
            ->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(__CLASS__, 'follows', 'following_user_id', 'follower_user_id')
            ->withTimestamps();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'designations.name' => '',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => $this->first_name.' '.$this->last_name);
    }
}

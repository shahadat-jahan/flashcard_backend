<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PostDeclineReason extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'post_id',
        'reason',
        'declined_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function declineBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declined_by');
    }
}

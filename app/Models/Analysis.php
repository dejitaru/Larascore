<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ANALYZING = 'analyzing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'repo_owner',
        'repo_name',
        'status',
        'score',
        'metrics_json',
        'recommendations_json',
        'callback_token',
    ];

    protected $casts = [
        'metrics_json' => 'array',
        'recommendations_json' => 'array',
        'score' => 'integer',
    ];
}

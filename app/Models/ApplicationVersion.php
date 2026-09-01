<?php

namespace App\Models;

use Database\Factories\ApplicationVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationVersion extends Model
{
    /** @use HasFactory<ApplicationVersionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'application_id',
        'version_number',
        'status',
        'content',
        'created_by',
        'submitted_at',
        'submitted_by',
        'revision_reason',
        'supersedes_version_id',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'submitted_at' => 'immutable_datetime',
            'metadata' => 'array',
            'version_number' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function supersedesVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_version_id');
    }
}

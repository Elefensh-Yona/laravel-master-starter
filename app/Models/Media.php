<?php

namespace App\Models;

use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uploaded_by',
        'attachable_type',
        'attachable_id',
        'collection',
        'disk',
        'directory',
        'path',
        'original_name',
        'stored_name',
        'extension',
        'mime_type',
        'size',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}

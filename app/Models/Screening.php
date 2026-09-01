<?php

namespace App\Models;

use Database\Factories\ScreeningFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Screening extends Model
{
    /** @use HasFactory<ScreeningFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'application_id',
        'application_version_id',
        'validation_id',
        'status',
        'outcome',
        'screened_by',
        'completed_at',
        'rationale',
        'reopened_at',
        'reopened_by',
        'reopen_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'immutable_datetime',
            'reopened_at' => 'immutable_datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function applicationVersion(): BelongsTo
    {
        return $this->belongsTo(ApplicationVersion::class, 'application_version_id');
    }

    public function validation(): BelongsTo
    {
        return $this->belongsTo(ApplicationValidation::class, 'validation_id');
    }

    public function screener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'screened_by');
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }
}

<?php

namespace App\Models;

use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'primary_owner_id',
        'applicant_type',
        'status',
        'reference',
        'submitted_at',
        'current_version_id',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'immutable_datetime',
            'metadata' => 'array',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function primaryOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_owner_id');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(ApplicationVersion::class, 'current_version_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ApplicationMember::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ApplicationVersion::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(ApplicationValidation::class);
    }

    public function screenings(): HasMany
    {
        return $this->hasMany(Screening::class);
    }
}

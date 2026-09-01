<?php

namespace App\Models;

use Database\Factories\ProgramEligibilityRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramEligibilityRule extends Model
{
    /** @use HasFactory<ProgramEligibilityRuleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'program_id',
        'key',
        'label',
        'rule_type',
        'configuration',
        'position',
        'is_required',
        'is_enabled',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'is_required' => 'boolean',
            'is_enabled' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}

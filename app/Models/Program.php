<?php

namespace App\Models;

use App\Enums\ProgramType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'funding_source',
        'budget',
        'application_deadline',
        'max_family_income',
        'min_gpa',
        'coordinator_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProgramType::class,
            'application_deadline' => 'date',
            'budget' => 'decimal:2',
            'max_family_income' => 'decimal:2',
            'min_gpa' => 'decimal:2',
        ];
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function isOpen(): bool
    {
        return $this->application_deadline->isFuture() || $this->application_deadline->isToday();
    }
}

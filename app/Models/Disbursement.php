<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'seq_no',
        'amount',
        'disbursement_date',
        'semester',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'disbursement_date' => 'date',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}

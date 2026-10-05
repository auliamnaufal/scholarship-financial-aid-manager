<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nim',
        'faculty',
        'study_program',
        'gpa',
        'year_enrolled',
        'phone',
        'address',
        'family_income',
        'parent_occupation',
        'dependents_count',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
    ];

    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'family_income' => 'decimal:2',
            'dependents_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** A disbursement needs somewhere to send the money. */
    public function hasBankAccount(): bool
    {
        return filled($this->bank_name) && filled($this->bank_account_number);
    }

    /**
     * Which biodata fields are still blank, so the student can be nudged before
     * they try to apply and the coordinator can see why a payment is stuck.
     */
    public function missingFields(): array
    {
        $labels = [
            'nim' => __('Student number'),
            'faculty' => __('Faculty'),
            'study_program' => __('Study programme'),
            'phone' => __('Phone number'),
            'address' => __('Address'),
            'family_income' => __('Family income'),
            'bank_name' => __('Bank name'),
            'bank_account_number' => __('Bank account number'),
            'bank_account_holder' => __('Account holder'),
        ];

        return array_values(array_filter(
            $labels,
            fn (string $field) => blank($this->{$field}),
            ARRAY_FILTER_USE_KEY,
        ));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = ['user_id', 'book_id', 'loan_date', 'due_date', 'returned_at', 'status', 'fine'];
    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'returned_at' => 'date',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}

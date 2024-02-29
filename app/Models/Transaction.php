<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUlids;

    protected $primaryKey = 'ulid';

    protected $fillable = [
        'account_ulid',
        'to_account_ulid',
        'category_ulid',
        'user_ulid',
        'date',
        'amount',
        'title',
        'description'
    ];

    public function user(): BelongsTo
    {
        return  $this->belongsTo(User::class);
    }
    public function category(): BelongsTo
    {
        return  $this->belongsTo(Category::class);
    }
    public function account(): BelongsTo
    {
        return  $this->belongsTo(Account::class);
    }
    public function toAccount(): BelongsTo
    {
        return  $this->belongsTo(Account::class, 'to_account_id');
    }
}

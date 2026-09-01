<?php

declare(strict_types=1);

namespace App\Modules\Credits\Models;

use App\Modules\Credits\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $fillable = [
        'credit_account_id',
        'type',
        'amount',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
        'project_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'integer',
            'balance_after' => 'integer',
            'project_id' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CreditAccount::class, 'credit_account_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['business_id', 'balance'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function hasEnoughCredits(int $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Add credits. Wrapped in a DB transaction with a row lock so concurrent
     * webhook retries / double-clicks on "top up" can't double-credit.
     */
    public function credit(int $amount, string $reason, array $meta = []): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $reason, $meta) {
            $wallet = self::query()->lockForUpdate()->findOrFail($this->id);
            $wallet->increment('balance', $amount);

            return $wallet->transactions()->create([
                'type' => 'credit',
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'reason' => $reason,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * Deduct credits (e.g. one certificate issued). Throws if balance is
     * insufficient — catch this in the controller and redirect to the
     * plans/top-up page rather than letting it bubble up as a 500.
     */
    public function debit(int $amount, string $reason, array $meta = []): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $reason, $meta) {
            $wallet = self::query()->lockForUpdate()->findOrFail($this->id);

            if ($wallet->balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance.');
            }

            $wallet->decrement('balance', $amount);

            return $wallet->transactions()->create([
                'type' => 'debit',
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'reason' => $reason,
                'meta' => $meta,
            ]);
        });
    }

    /**
     * Cap unused credits at plan allotment * rollover multiplier so credits
     * don't pile up forever on renewal. Call this from the subscription
     * renewal job, before crediting the new period's allotment.
     */
    public function capRolloverBeforeRenewal(int $cap): void
    {
        if ($this->balance > $cap) {
            $this->debit($this->balance - $cap, 'rollover_expire');
        }
    }
}

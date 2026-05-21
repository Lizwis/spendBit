<?php

namespace App\Services;

use App\Models\LedgerEntry;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * CREDIT USER (money in)
     */
    public function credit(
        int $userId,
        float $amount,
        string $referenceId,
        string $type,
        array $metadata = []
    ) {
        return DB::transaction(function () use (
            $userId,
            $amount,
            $referenceId,
            $type,
            $metadata
        ) {

            // 1. Prevent double processing (VERY IMPORTANT for blockchain)
            $exists = LedgerEntry::where('reference_id', $referenceId)
                ->where('direction', 'credit')
                ->exists();

            if ($exists) {
                return null; // idempotency guard
            }

            // 2. Create ledger entry
            $entry = LedgerEntry::create([
                'user_id' => $userId,
                'type' => $type,
                'reference_id' => $referenceId,
                'direction' => 'credit',
                'amount' => $amount,
                'currency' => 'USDC',
                'status' => 'confirmed',
                'metadata' => $metadata,
            ]);

            // 3. Update cached balance (optional but fast)
            $this->updateCachedBalance($userId);

            return $entry;
        });
    }

    /**
     * DEBIT USER (money out)
     */
    public function debit(
        int $userId,
        float $amount,
        string $referenceId,
        string $type,
        array $metadata = []
    ) {
        return DB::transaction(function () use (
            $userId,
            $amount,
            $referenceId,
            $type,
            $metadata
        ) {

            // 1. Prevent duplicates
            $exists = LedgerEntry::where('reference_id', $referenceId)
                ->where('direction', 'debit')
                ->exists();

            if ($exists) {
                return null;
            }

            // 2. Check balance BEFORE debit
            $balance = $this->getBalance($userId);

            if ($balance < $amount) {
                throw new \Exception("Insufficient balance");
            }

            // 3. Create debit entry
            $entry = LedgerEntry::create([
                'user_id' => $userId,
                'type' => $type,
                'reference_id' => $referenceId,
                'direction' => 'debit',
                'amount' => $amount,
                'currency' => 'USDC',
                'status' => 'confirmed',
                'metadata' => $metadata,
            ]);

            // 4. Update cached balance
            $this->updateCachedBalance($userId);

            return $entry;
        });
    }

    /**
     * GET BALANCE (source of truth calculation)
     */
    public function getBalance(int $userId): float
    {
        $credits = LedgerEntry::where('user_id', $userId)
            ->where('direction', 'credit')
            ->sum('amount');

        $debits = LedgerEntry::where('user_id', $userId)
            ->where('direction', 'debit')
            ->sum('amount');

        return (float) ($credits - $debits);
    }

    /**
     * OPTIONAL: cache balance on users table
     */
    private function updateCachedBalance(int $userId): void
    {
        $balance = $this->getBalance($userId);

        \App\Models\User::where('id', $userId)
            ->update(['balance_usdc' => $balance]);
    }

    /**
     * RESOLVE ID (useful for blockchain/webhook mapping)
     */
    public function findByReference(string $referenceId)
    {
        return LedgerEntry::where('reference_id', $referenceId)->first();
    }
}

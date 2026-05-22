<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CryptoDepositController extends Controller
{
    public function store(Request $request)
    {
        /**
         * =====================================
         * AUTH CHECK (SANCTUM)
         * =====================================
         */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

        /**
         * =====================================
         * INPUT
         * =====================================
         */
        $txHash = strtolower(trim($request->tx_hash));
        $wallet = strtolower(trim($request->wallet));

        if (!$txHash || !$wallet) {
            return response()->json([
                'error' => 'Missing data'
            ], 422);
        }

        /**
         * =====================================
         * PREVENT DUPLICATE TX (GLOBAL SAFETY)
         * =====================================
         */
        $exists = Deposit::where('tx_hash', $txHash)->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Transaction already processed'
            ], 400);
        }

        /**
         * =====================================
         * CONFIG
         * =====================================
         */
        $rpcUrl = config('crypto.rpc_url');
        $tokenAddress = strtolower(config('crypto.token_address'));
        $treasury = strtolower(config('crypto.treasury_wallet'));

        /**
         * =====================================
         * FETCH TX RECEIPT
         * =====================================
         */
        $rpcResponse = Http::post($rpcUrl, [
            "jsonrpc" => "2.0",
            "id" => 1,
            "method" => "eth_getTransactionReceipt",
            "params" => [$txHash],
        ])->json();

        $receipt = $rpcResponse['result'] ?? null;

        if (!$receipt) {
            return response()->json([
                'error' => 'Transaction not found'
            ], 400);
        }

        if (($receipt['status'] ?? null) !== '0x1') {
            return response()->json([
                'error' => 'Transaction failed'
            ], 400);
        }

        /**
         * =====================================
         * VALIDATE ERC20 TRANSFER
         * =====================================
         */
        $valid = false;
        $amountRaw = 0;

        foreach ($receipt['logs'] ?? [] as $log) {

            if (strtolower($log['address'] ?? '') !== $tokenAddress) {
                continue;
            }

            $topics = $log['topics'] ?? [];

            if (!isset($topics[2])) {
                continue;
            }

            $to = '0x' . substr($topics[2], 26);

            if (strtolower($to) !== $treasury) {
                continue;
            }

            $amountRaw = hexdec($log['data'] ?? '0x0');
            $valid = true;
            break;
        }

        if (!$valid) {
            return response()->json([
                'error' => 'Invalid token transfer'
            ], 400);
        }

        /**
         * =====================================
         * CONVERT AMOUNT (USDC 6 DECIMALS)
         * =====================================
         */
        $amount = $amountRaw / 1000000;

        /**
         * =====================================
         * STORE DEPOSIT (USER OWNERSHIP ONLY)
         * =====================================
         */
        $deposit = Deposit::create([
            'user_id' => $user->id,
            'wallet' => $wallet, // metadata only
            'tx_hash' => $txHash,
            'amount' => $amount,
            'token_address' => $tokenAddress,
            'network' => 'polygon-amoy',
            'status' => 'confirmed',
        ]);

        /**
         * =====================================
         * RESPONSE
         * =====================================
         */
        return response()->json([
            'success' => true,
            'deposit_id' => $deposit->id,
            'tx_hash' => $txHash,
            'wallet' => $wallet,
            'amount' => $amount,
            'user_id' => $user->id,
            'network' => 'polygon-amoy',
            'status' => 'confirmed',
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $deposits = Deposit::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get([
                'id',
                'tx_hash',
                'amount',
                'wallet',
                'network',
                'status',
                'created_at'
            ]);

        return response()->json([
            'success' => true,
            'data' => $deposits
        ]);
    }
}

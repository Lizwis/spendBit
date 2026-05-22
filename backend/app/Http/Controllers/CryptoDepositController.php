<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CryptoDepositController extends Controller
{
    public function store(Request $request)
    {
        $txHash = strtolower($request->tx_hash);
        $wallet = strtolower($request->wallet);

        /**
         * =====================================
         * VALIDATE INPUT
         * =====================================
         */
        if (!$txHash || !$wallet) {
            return response()->json([
                'error' => 'Missing data'
            ], 422);
        }

        /**
         * =====================================
         * PREVENT DUPLICATE TX PROCESSING
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
         * LOAD CONFIG FROM ENV
         * =====================================
         */
        $rpcUrl = config('crypto.rpc_url');

        $tokenAddress = strtolower(
            config('crypto.token_address')
        );

        $treasury = strtolower(
            config('crypto.treasury_wallet')
        );

        /**
         * =====================================
         * FETCH TX RECEIPT FROM POLYGON
         * =====================================
         */
        $rpcResponse = Http::post($rpcUrl, [
            "jsonrpc" => "2.0",
            "id" => 1,
            "method" => "eth_getTransactionReceipt",
            "params" => [$txHash],
        ])->json();

        $receipt = $rpcResponse['result'] ?? null;

        /**
         * =====================================
         * VALIDATE RECEIPT
         * =====================================
         */
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
         * VALIDATE ERC20 TRANSFER LOGS
         * =====================================
         */
        $logs = $receipt['logs'] ?? [];

        $valid = false;
        $amountRaw = null;

        foreach ($logs as $log) {

            /**
             * Ensure correct token contract
             */
            if (
                strtolower($log['address'] ?? '') !== $tokenAddress
            ) {
                continue;
            }

            /**
             * ERC20 Transfer event:
             * Transfer(address,address,uint256)
             */

            $topics = $log['topics'] ?? [];
            $data = $log['data'] ?? null;

            /**
             * Ensure recipient exists
             */
            if (!isset($topics[2])) {
                continue;
            }

            /**
             * Extract recipient address
             */
            $to = '0x' . substr($topics[2], 26);

            /**
             * Ensure treasury wallet received tokens
             */
            if (strtolower($to) !== $treasury) {
                continue;
            }

            /**
             * Convert hex amount
             */
            $amountRaw = hexdec($data);

            $valid = true;

            break;
        }

        /**
         * =====================================
         * INVALID TRANSFER
         * =====================================
         */
        if (!$valid) {
            return response()->json([
                'error' => 'Invalid token transfer'
            ], 400);
        }

        /**
         * =====================================
         * TOKEN DECIMALS
         * =====================================
         * Assuming 6 decimals like USDC
         */
        $amount = $amountRaw / 1000000;

        /**
         * =====================================
         * STORE VERIFIED DEPOSIT
         * =====================================
         */
        $deposit = Deposit::create([
            'wallet' => $wallet,
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
            'network' => 'polygon-amoy',
            'status' => 'confirmed',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CryptoDepositController extends Controller
{
    public function store(Request $request)
    {
        $txHash = $request->tx_hash;
        $wallet = strtolower($request->wallet);

        if (!$txHash || !$wallet) {
            return response()->json([
                'error' => 'Missing data'
            ], 422);
        }


        $rpcUrl = config('crypto.rpc_url');
        $tokenAddress = strtolower(config('crypto.token_address'));
        $treasury = strtolower(config('crypto.treasury_wallet'));

        /**
         *  STEP 1: Fetch transaction receipt from Polygon Amoy
         */
        $rpcResponse = Http::post($rpcUrl, [
            "jsonrpc" => "2.0",
            "id" => 1,
            "method" => "eth_getTransactionReceipt",
            "params" => [$txHash],
        ])->json();

        $receipt = $rpcResponse['result'] ?? null;

        if (!$receipt || $receipt['status'] !== '0x1') {
            return response()->json([
                'error' => 'Transaction not confirmed or failed'
            ], 400);
        }

        /**
         *  STEP 2: Validate logs (ERC20 Transfer event)
         */
        $logs = $receipt['logs'] ?? [];



        $valid = false;
        $amount = null;

        foreach ($logs as $log) {
            if (strtolower($log['address']) !== $tokenAddress) {
                continue;
            }

            /**
             * ERC20 Transfer signature:
             * Transfer(address,address,uint256)
             */
            $data = $log['data'];

            if ($data && $log['topics'][2] ?? null) {
                $to = '0x' . substr($log['topics'][2], 26);

                if (strtolower($to) === $treasury) {
                    $valid = true;
                    $amount = hexdec($data);
                }
            }
        }

        if (!$valid) {
            return response()->json([
                'error' => 'Invalid token transfer'
            ], 400);
        }

        /**
         *  STEP 3: Store deposit (REAL SOURCE OF TRUTH)
         */
        // DB insert
        // Deposit::create([...])

        return response()->json([
            'success' => true,
            'tx' => $txHash,
            'wallet' => $wallet,
            'amount_raw' => $amount
        ]);
    }
}

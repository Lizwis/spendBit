<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LedgerService;
use App\Models\CryptoDeposit;
use App\Models\User;



class CryptoWebhookController extends Controller
{

    public function handle(Request $request)
    {
        $data = $request->all();

        $txHash = $data['hash'];

        if (CryptoDeposit::where('tx_hash', $txHash)->exists()) {
            return response()->json(['status' => 'duplicate']);
        }

        if ($data['toAddress'] !== env('TREASURY_WALLET')) {
            return response()->json(['status' => 'ignored']);
        }

        $user = User::where('wallet_address', $data['fromAddress'])->first();

        if (!$user) {
            return response()->json(['status' => 'user_not_found']);
        }

        CryptoDeposit::create([
            'user_id' => $user->id,
            'tx_hash' => $txHash,
            'from_address' => $data['fromAddress'],
            'to_address' => $data['toAddress'],
            'amount' => $data['value'],
            'status' => 'confirmed',
            'chain' => 'polygon',
        ]);

        app(LedgerService::class)->credit(
            $user->id,
            $data['value'],
            $txHash,
            'crypto_deposit',
            [
                'chain' => 'polygon',
                'from' => $data['fromAddress']
            ]
        );

        return response()->json(['status' => 'ok']);
    }
}

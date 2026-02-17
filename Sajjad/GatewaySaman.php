<?php

namespace App\Clients\Gateways;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;

class GatewaySaman
{
    public function getToken(int $resNum = 12342123123, int $amount = 10000, $gateway, string $phone = '09108443787')
    {
        $response = Http::withBasicAuth('testuser', 'testpass')
            ->post('http://food.imhh.ir/baharan/transfer.php?url=' . $gateway->getTokenUrl(),
                $gateway->getBody([
                    'resNum' => $resNum,
                    'amount' => $amount,
                    'phone' => $phone,
                ]));

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json([
                'error' => $response->status(),
                'message' => $response->body(),
            ], $response->status());
        }
    }

    public function verifyTransaction(string $RefNum, int $terminalId)
    {

        $response = Http::withBasicAuth('testuser', 'testpass')
            ->post('http://food.imhh.ir/baharan/transfer.php?url=https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction', [
                'TerminalNumber' => strval($terminalId),
                'RefNum' => $RefNum,
            ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json([
                'error' => $response->status(),
                'message' => $response->body(),
            ], $response->status());
        }
    }

    public function reverseTransaction(string $RefNum, int $terminalId)
    {
        $response = Http::withBasicAuth('testuser', 'testpass')
            ->post('http://food.imhh.ir/baharan/transfer.php?url=https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/ReverseTransaction', [
                'TerminalNumber' => strval($terminalId),
                'RefNum' => $RefNum,
            ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return response()->json([
                'error' => $response->status(),
                'message' => $response->body(),
            ], $response->status());
        }
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;


/**
 * Manage All interactions with the Flutterwave API in this script
 */
class Flutterwave
{
    /**
     * Get a List of all the Banks to get thier code for transfers
     */
    public static function listBanks()
    {
        $bearer_token = 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY');

        $response = Http::withHeaders([
            'Authorization' => $bearer_token,
        ])->get('https://api.flutterwave.com/v3/banks/NG');

        return $response->json();
    }

    /**
     * Find Bank With String sent
     */

    public static function findBankCode($bank_name)
    {
        $banks = self::listBanks()['data'];

        foreach($banks as $bank) {
            if(preg_match('/'.$bank_name.'/i', $bank['name'] )) {
                return $bank;
            }

        }
    }


    /**
     * 
     */

    public static function verifyAccountDetails($bank_code, $account_number)
    {
        $bearer_token = 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY');
       
            $response = Http::withHeaders([
                'Authorization' => $bearer_token,
            ])
                ->post('https://api.flutterwave.com/v3/accounts/resolve', [
                    'account_number' =>  $account_number,
                    'account_bank' => $bank_code,
                ]);

            return $response->json();
       
    }

    /**
     * 
     */
    public static function chargeBankAcc($ref, $amount, $bank_code, $account_number, $fullname,  $email)
    {
        $bearer_token = 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY');

        $response = Http::withHeaders([
            'Authorization' => $bearer_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post('https://api.flutterwave.com/v3/charges?type=debit_ng_account', [
            'email' => $email,
            'amount' => $amount,
            "tx_ref" => $ref,
            "account_bank" => $bank_code,
            "account_number" => $account_number,
            "currency" => "NGN",
            "fullname" => $fullname,
        ]);

        

        return $response->json();
    }

    /**
     * Validate charge
     */
    public static function validateCharge($paymentRef, $type, $otp)
    {
        $bearer_token = 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY');

        $response = Http::withHeaders([
            'Authorization' => $bearer_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post('https://api.flutterwave.com/v3/validate-charge', [
            'otp' => $otp,
            'type' => $type,
            'flw_ref' => $paymentRef,
        ]);

        return $response->json();
    }


    /**
     * 
     */

    public static function initiateTransfer($reason, $amount, $recipient_bank, $recipient_acc_num, $reference)
    {
        $bearer_token = 'Bearer ' . env('FLUTTERWAVE_SECRET_KEY');

        $response = Http::withHeaders([
            'Authorization' => $bearer_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->post('https://api.flutterwave.com/v3/transfers', [
            "account_bank" => $recipient_bank,
            "account_number" => $recipient_acc_num,
            "amount" => $amount,
            "narration" => $reason,
            "currency" => "NGN",
            "reference" => $reference,
            "callback_url" => env('FLUTTERWAVE_WEBHOOK'),
            "debit_currency" => "NGN"
        ]);


        return $response->json();
    }
}

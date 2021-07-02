<?php

namespace App\Services;

use App\Models\Transfer;

/**
 * Manage Business Logic in this script file for reusuability
 */
class TransferService{

    public static function createTransfer($user_id, $sender_acc_num, $sender_bank, $recipient_name, $recipient_bank, $recipient_acc_num, $amount, $payment_reference ){
        $transfer = Transfer::create([
            'user_id' => $user_id,
            'sender_acc_num' => $sender_acc_num,
            'sender_bank' => $sender_bank,
            'recipient_name' => $recipient_name,
            'recipient_acc_num' => $recipient_acc_num,
            'recipient_bank' => $recipient_bank,
            'amount' => $amount,
            'payment_reference' => $payment_reference,
        ]);

        if ($transfer){
            return $transfer;
        }

        return null;
    }

    public static function userTransfers($user_id, $term){

        $transfers =  Transfer::where('user_id', $user_id)
                ->when($term, function ($query, $term) {
                    return $query->where('recipient_name', 'like', '%' . $term . '%')
                        ->orWhere('amount', 'like', '%' . $term . '%')
                        ->orWhere('payment_reference', 'like', '%' . $term . '%')
                        ->orWhere('transfer_reference', 'like', '%' . $term . '%')
                        ->orWhere('status', 'like', '%' . $term . '%');
                })->get();
        if ($transfers){
            return $transfers;
            }
        
        return null;

    }

}
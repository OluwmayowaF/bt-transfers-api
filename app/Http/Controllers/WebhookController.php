<?php

namespace App\Http\Controllers;

use App\Mail\NotificationMails;
use App\Models\Transfer;
use App\Models\User;
use App\Support\Flutterwave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WebhookController extends Controller
{
    /*The Method manages request gotten from the flutterwave payment/ tramsaction webhook*/
    public function managePayments(Request $request){

        if($request->event == "charge.completed" && $request->data['status'] == 'successful' ){

            // Find Transfer Information to initiate transfer
            $transfer = Transfer::where('payment_reference', $request->data['flw_ref'])->first();
            $recipient_bank = Flutterwave::findBankCode($transfer->recipient_bank);
            $reference = 'BT-'.Carbon::now()->timestamp.'_NTI';


           $flutter_transfer = Flutterwave::initiateTransfer('stuff', $transfer->amount,  $recipient_bank['code'], $transfer->recipient_acc_num, $reference); 
           if( !$flutter_transfer){
                   return response()->json([
                'status' =>  false,
            ], 400);
           }

           //update trasfer information for user
           $transfer->status = 'initiated';
           $transfer->transfer_reference = $flutter_transfer['data']['reference'];
           $transfer->save();
           $sender = User::find($transfer->user_id);

           $details = [
            'user' =>  $sender,
            'info' => 'Your transfer to '.$transfer->recipient_name.' has now been initiated! Thanks for your business'
        ];

        Mail::to($sender->email)->send(new NotificationMails($details));

           return response()->json([
            'status' =>  false,
            ], 200);


        }


    }
    
    /*The Method manages request gotten from the flutterwave transfer webhook*/

    public function manageTransfers(Request $request){
        if($request->event == "transfer.completed"){
            $transfer = Transfer::where('transfer_reference', $request->data['reference'])->first();

            //Update Transfer and Mail to user
            $transfer->status =  $request->data['status'];
            $transfer->save();
            //send notification to user on transfer
            $sender = User::find($transfer->user_id);

            $details = [
             'user' =>  $sender,
             'info' => 'Your transfer to '.$transfer->recipient_name.' has now been completed with status => '.$request->data['status'].'! Thanks for your business'
         ];

         Mail::to($sender->email)->send(new NotificationMails($details));

            return response()->json([
                'status' =>  false,
                ], 200);


        }
    }
}

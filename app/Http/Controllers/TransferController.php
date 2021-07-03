<?php

namespace App\Http\Controllers;
use App\Http\Requests\CreateTransfer;
use App\Mail\NotificationMails;
use App\Support\Flutterwave;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\TransferService;

class TransferController extends Controller
{
    /**
     * This Method accepts users paymennt and creates transfer record,
     * Transfer is made when response is recieved from flutter webhook
     */
    public function initiateTransfer(CreateTransfer $request)
    {

            // Get and verify the validitity of  bank code from the submitted bank name
            $recipient_bank_code = Flutterwave::findBankCode($request->bank);

            if (!$recipient_bank_code) {
                return response()->json([
                    'status' =>  false, 
                    'error' => 'Recipient bank was not found, use the bank list of supported banks',
                    'data' => null
                ], 400);

            } 

            $recipientAccValid = Flutterwave::verifyAccountDetails($recipient_bank_code['code'], $request->account_number);

            if ($recipientAccValid['status'] === 'error') {
                return response()->json([
                    'status' => false,
                    'error' => $recipientAccValid['message'],
                    'data' => null
                ], 400);
            }

            $reference = 'BT-' . Carbon::now()->timestamp . '_NT';

            $transfer = Flutterwave::initiateTransfer($request->description, $request->amount, $recipient_bank_code['code'], $request->account_number, $reference);

            if ($transfer['status'] !== 'success') {
                return response()->json([
                    'status' =>  false,
                    'error' => $transfer['message'],
                    'data' => null
                ], 400);
            }

            $save_transfer = TransferService::createTransfer(Auth::user()->id, 
                        $recipientAccValid['data']['account_name'], 
                        $request->bank, 
                        $request->account_number,
                        $request->amount, $reference, 
                        $request->description);

            $details = [
                'user' => Auth::user(),
                'info' => 'Your transfer to ' . $save_transfer->recipient_name . ' has been initiated, you will recieve a mail on completion '
            ];

            Mail::to(Auth::user()->email)->send(new NotificationMails($details));

            return response()->json([
                'status' =>  true,
                'message' => $transfer['message'],
                'data' => $save_transfer,
            ], 200);

    }

    public function transferHistory(Request $request)
    {
       
            $user_id = Auth::user()->id;

            $term = $request->term;

            $transfers =  TransferService::userTransfers($user_id, $term);

            if(!$transfers){
                return response()->json([
                    'status' =>  false,
                    'error' => 'We are unable to process your request at the moment, please try again later',
                ], 500);
            }
        
            return response()->json([
                'status' =>  true,
                'data' => $transfers,
            ], 200);

        
    }
}

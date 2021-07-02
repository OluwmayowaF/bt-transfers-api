<?php

namespace App\Http\Controllers;
use App\Http\Requests\CreateTransfer;
use App\Mail\NotificationMails;
use App\Models\Transfer;
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
        try {  

            // Get and verify the validitity of  bank code from the submitted bank name
            $user_bank_code = Flutterwave::findBankCode($request->user_bank);
            if (!$user_bank_code) {
                return response()->json([
                    'status' =>  false, 
                    'error' => 'User bank name is invalid'
                ], 400);
            } 

            $recipient_bank_code = Flutterwave::findBankCode($request->recipient_bank);
            if (!$recipient_bank_code) {
                return response()->json([
                    'status' =>  false, 
                    'error' => 'Recipient bank name is invalid'
                ], 400);

            } 

            // Check Validity of both accounts 
            $userAccValid = Flutterwave::verifyAccountDetails($user_bank_code['code'], $request->user_acc_num);

            if (!$userAccValid['status']) {
                return response()->json([
                    'message' => 'User\'s account details are invalid',
                    'error' => $userAccValid['message']
                ], 400);
            }

            $recipientAccValid = Flutterwave::verifyAccountDetails($recipient_bank_code['code'], $request->recipient_acc_num);
            if (!$recipientAccValid['status']) {
                return response()->json([
                    'message' => 'Recipeint\'s account details are invalid',
                    'error' => $recipientAccValid['message']
                ], 400);
            }

            $reference = 'BT-' . Carbon::now()->timestamp . '_NT';

            $payment = Flutterwave::chargeBankAcc($reference, $request->amount, $user_bank_code['code'], $request->user_acc_num, Auth::user()->name, Auth::user()->email);

            if ($payment['status'] !== 'success') {

                return response()->json([
                    'status' =>  false,
                    'error' => $payment['message']
                ], 400);
            }

            $transfer = TransferService::createTransfer(Auth::user()->id, 
                        $request->user_acc_num, 
                        $request->user_bank, 
                        $recipientAccValid['data']['account_name'], 
                        $request->recipient_bank, 
                        $request->recipient_acc_num,
                        $request->amount, $payment['data']['flw_ref']);

            $details = [
                'user' => Auth::user(),
                'info' => 'Your transfer to ' . $transfer->recipient_name . ' will be initiated once your payment of ' . $transfer->amount . ' Naira is verified'
            ];

            Mail::to(Auth::user()->email)->send(new NotificationMails($details));

            return response()->json([
                'status' =>  true,
                'message' => 'Payment successful you will recieve a mail when confirmed and transfer is initiated, keep this reference (' . $payment['data']['flw_ref'] . ') as proof if payment is not recieved',
            ], 200);

        } catch (\Throwable $err) {
            return response()->json([
                'message' => 'We are unable to process your request right now, kindly check your internet connection and try again in a few seconds',
                'error' => $err->getMessage()

            ], 500);
        }
    }
    
    /*  Required if running flutterwave from a live account 
    public function validatePayment(Request $request)
    {

        try {
            $validated = Flutterwave::validateCharge($request->type, $request->reference, $request->otp);

            if ($validated['status'] !== 'error') {
                return response()->json([
                    'status' => true,
                    'message' => $validated['message'],
                    'data' => $validated['data']

                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => $validated['message'],
            ], 400);

        } catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => 'We are unable to process your request right now, kindly check your internet connection and try again in a few seconds',
                'error' => $err->getMessage()

            ], 500);
        }
    }*/

    public function transferHistory(Request $request)
    {
        try {
            $user_id = Auth::user()->id;

            $term = $request->term;

            $transfers =  TransferService::userTransfers($user_id, $term);
        
            return response()->json([
                'status' =>  true,
                'data' => $transfers,
            ], 200);

        } catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => 'We are unable to process your request right now, kindly check your internet connection and try again in a few seconds. if issue per',
                'error' => $err->getMessage()

            ], 500);
        }
    }
}

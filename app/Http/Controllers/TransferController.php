<?php

namespace App\Http\Controllers;

use App\Mail\NotificationMails;
use App\Models\Transfer;
use App\Support\Flutterwave;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TransferController extends Controller
{
    public function initiateTransfer(Request $request)
    {
        try {
            $request->validate([
                'user_bank' => 'required',
                'user_acc_num' => 'required',
                'recipient_acc_num' => 'required',
                'recipient_bank' => 'required',
                'amount' => 'required'
            ]);

            $recipient_bank_code = Flutterwave::findBankCode($request->recipient_bank);
            $user_bank_code = Flutterwave::findBankCode($request->user_bank);

            if (!$recipient_bank_code) return response()->json(['status' =>  false, 'error' => 'Recipient bank name is invalid'], 400);
            if (!$user_bank_code) return response()->json(['status' =>  false, 'error' => 'User bank name is invalid'], 400);


            // Check Validity of both accounts 
            $userAccValid = Flutterwave::verifyAccountDetails($user_bank_code['code'], $request->user_acc_num);
            $recipientAccValid = Flutterwave::verifyAccountDetails($recipient_bank_code['code'], $request->recipient_acc_num);

            if (!$userAccValid['status']) {
                return response()->json([
                    'message' => 'User\'s account details are invalid',
                    'error' => $userAccValid['message']
                ], 400);
            }

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

            $transfer = Transfer::create([
                'user_id' => Auth::user()->id,
                'sender_acc_num' => $request->user_acc_num,
                'sender_bank' => $request->user_bank,
                'recipient_name' => $recipientAccValid['data']['account_name'],
                'recipient_acc_num' => $request->recipient_acc_num,
                'recipient_bank' => $request->recipient_bank,
                'amount' => $request->amount,
                'payment_reference' => $payment['data']['flw_ref'],
            ]);

            $details = [
                'user' => Auth::user(),
                'info' => 'Your transfer to ' . $transfer->recipient_name . ' will be initiated once your payment of ' . $transfer->amount . ' is verified'
            ];

            Mail::to(Auth::user()->email)->send(new NotificationMails($details));



            return response()->json([
                'status' =>  true,
                'message' => 'Payment successful you will recieve a mail when confirmed and transfer is initiated, keep this reference (' . $payment['data']['flw_ref'] . ') as proof if payment is not recieved',
            ], 200);
            
        } catch (\Throwable $err) {
            return response()->json([
                'message' => 'Unable to process request, kindly check your internet connection and try again',
                'error' => $err->getMessage()

            ], 500);
        }
    }

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
                'message' => 'Unable to process request, kindly check your internet connection and try again',
                'error' => $err->getMessage()

            ], 500);
        }
    }


    public function transferHistory()
    {
        try {
            $user_id = Auth::user()->id;

            $transfers = Transfer::where('user_id', $user_id)->where('status', '!=', 'queued')->get();

            return response()->json([
                'status' =>  true,
                'data' => $transfers,
            ], 200);
        } catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to process request, kindly check your internet connection and try again',
                'error' => $err->getMessage()

            ], 500);
        }
    }

    public function searchTransfer(Request $request)
    {
        $term = $request->term;
        try {
            $user_id = Auth::user()->id;

            $transfers = Transfer::where('user_id', $user_id)
                ->when($term, function ($query, $term) {
                    return $query->where('recipient_name', 'like', '%' . $term . '%')
                        ->orWhere('amount', 'like', '%' . $term . '%')
                        ->orWhere('payment_reference', 'like', '%' . $term . '%')
                        ->orWhere('transfer_reference', 'like', '%' . $term . '%')
                        ->orWhere('status', 'like', '%' . $term . '%');
                })->get();

            return response()->json([
                'status' =>  true,
                'data' => $transfers,
            ], 200);

        } catch (\Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to process request, kindly check your internet connection and try again',
                'error' => $err->getMessage()

            ], 500);
        }
    }
}

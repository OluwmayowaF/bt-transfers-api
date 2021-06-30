<?php

namespace App\Http\Controllers;

use App\Support\Flutterwave;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Return the list of banks 
     */
    public function getAllBanks(){
        $banks = Flutterwave::listBanks();
        return response()->json(['banks' =>  $banks], 200);
    }

}

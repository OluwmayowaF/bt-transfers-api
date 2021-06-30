<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\User;



class AuthController extends Controller
{
    
    /**
     * Register a new user.
     *
     * @param  $request(email, name, password)
     * @return response with user object and token for user
     */

     public function register(Request $request){
        $request->validate([
            'email' => 'required|email|unique:users',
            'firstname' => 'required',
            'lastname' => 'required',
            'password' => 'required'
        ]);


        DB::beginTransaction();


        try {

            
            $user = User::create([
                'name' => $request->firstname.' '.$request->lastname,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            DB::commit();
            return response()->json([
                'user' => $user, 
                'access_token' => $user->createToken($user)->plainTextToken], 
                201);

        }catch(\Throwable $err){
            DB::rollBack();
            return response()->json([
                'message' => 'Unable to process request, kindly check your internet connection and try again',
                'error' => $err->getMessage()

            ], 500);
        }
    
     }

     public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        try{
            
    
            $user = User::where('email', $request->email)->first();
    
            if(!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }
    
            return response()->json([
                'user' => $user, 
                'access_token' => $user->createToken($user)->plainTextToken], 
                200);

        }catch(\Throwable $err){
            DB::rollBack();
            return response()->json([
                'message' => 'Unable to process request, kindly check your internet connection and try again',
                'error' => $err->getMessage()

            ], 500);
        }
    
       

     }


}

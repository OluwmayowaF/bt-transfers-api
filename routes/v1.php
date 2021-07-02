<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WebhookController;
use App\Models\Transfer;

/** Auth Routes */
Route::prefix('/auth')->group( function(){
    Route::post('/register', [AuthController::class, 'register' ]);
    Route::post('/login', [AuthController::class, 'login' ]);
});

//Protected Routes for logged in users only
Route::middleware('auth:sanctum')->group(function(){
    Route::prefix('/transfers')->group(function(){
        Route::post('/', [TransferController::class, 'initiateTransfer']);
        Route::get('/', [TransferController::class, 'transferHistory']);
       // Route::post('/validatepayment', [TransferController::class, 'validatePayment']);
    });
});

/**External Route to get Bank Information */
Route::prefix('/banks')->group(function(){
    Route::get('/', [BankController::class, 'getAllBanks']);
});

/**External Route to manage incoming webhooks */
Route::prefix('/webhook')->group(function(){
    Route::post('/payments', [WebhookController::class, 'managePayments']);
    Route::post('/transfers', [WebhookController::class, 'manageTransfers']);
});

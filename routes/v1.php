<?php 
use App\Http\Controllers\Auth\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group( function(){
    Route::post('/register', [UserController::class, 'register' ]);
    Route::post('/login', [UserController::class, 'login' ]);
});

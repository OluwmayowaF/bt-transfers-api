<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::get('/', function () {
    return response()->json([
        'status' => true,
        'message' => 'Welcome to BT transfers API'
    ], 200);
});

// Route definitions should be done in the v1.php file in the routes folder
Route::prefix('v1')->group( function(){
        require_once "v1.php";
});



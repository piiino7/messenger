<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::middleware('auth:sanctum')->group(function(){
    Route::get('conversations', [Controllers\ConversationsController::class, 'index']);
    Route::get('conversations/{conversation}', [Controllers\ConversationsController::class, 'show']);
    Route::post('conversations/{conversation}/participants', [Controllers\ConversationsController::class, 'addParticipants']);
    Route::delete('conversations/{conversation}/participants', [Controllers\ConversationsController::class, 'removeParticipants']);
    Route::put('conversations/{conversation}/read', [Controllers\ConversationsController::class, 'markAsRead']);
    Route::put('conversations/{user}/online', [Controllers\ConversationsController::class, 'markLastOnline']);
    Route::delete('conversations/{conversation}/', [Controllers\ConversationsController::class, 'destroy']);

    Route::get('conversation/{id}/messages', [Controllers\MessagesController::class, 'index']);
    Route::post('messages', [Controllers\MessagesController::class, 'store'])->name('api.message.store');
    Route::put('messages/{message}/', [Controllers\MessagesController::class, 'update']);
    Route::delete('messages/{message}/', [Controllers\MessagesController::class, 'destroy']);

    Route::get('messenger/', [Controllers\MessengerController::class, 'getUsers']);
    Route::get('messenger/getMe', [Controllers\MessengerController::class, 'getMe']);
    Route::post('messenger/', [Controllers\MessengerController::class, 'store']);
});



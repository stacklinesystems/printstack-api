<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\PrintJobController;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/user', [AuthController::class, 'user']);

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/printers', [PrinterController::class, 'index']);

        Route::post('/printers', [PrinterController::class, 'store']);

        Route::post('/printers/sync', [PrinterController::class, 'sync']);

        Route::put('/printers/{id}', [PrinterController::class, 'update']);

        Route::post('/printers/{id}/active', function ($id, Request $request) {

            return Printer::where('id', $id)->update([
                'is_active' => $request->is_active
            ]);
        });

        Route::post('/print-jobs/{id}/status', [PrintJobController::class, 'updateStatus']);

        Route::post('/broadcasting/auth', function (Request $request) {

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $socketId = $request->socket_id;
            $channel  = $request->channel_name;

            /*
            |--------------------------------------------------------------------------
            | Validate printer ownership here
            |--------------------------------------------------------------------------
            */

            // Example:
            // private-print.DESKTOP-ABC

            $host = str_replace('private-print.', '', $channel);

            // Example validation:
            // only allow linked hostname

            // if ($user->hostname !== $host) {
            //     return response()->json([
            //         'message' => 'Unauthorized'
            //     ], 403);
            // }

            /*
            |--------------------------------------------------------------------------
            | Generate Soketi/Pusher auth signature
            |--------------------------------------------------------------------------
            */

            $stringToSign = $socketId . ':' . $channel;

            $signature = hash_hmac(
                'sha256',
                $stringToSign,
                env('PUSHER_APP_SECRET')
            );

            return response()->json([
                'auth' => env('PUSHER_APP_KEY') . ':' . $signature
            ]);
        });

        Route::post('/print-jobs', [PrintJobController::class, 'store']);
    });
});

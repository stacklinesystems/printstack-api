<?php

use App\Events\PrintJobCreated;
use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Rawilk\Printing\Receipts\ReceiptPrinter;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/print_job', function () {
    $job = PrintJob::create([
        'device_id' => 1,
        'printer_id' => 8,
        'type' => 'pdf',
        'content' => 'http://localhost:8000/test.pdf',
        'status' => 'pending',
        'attempts' => 0,
        'idempotency_key' => uniqid('job_')
    ]);

    $job->load(['printer', 'device']);

    $host = $job->device->name; // or printer hostname

    broadcast(new PrintJobCreated($job, $host));

    return response()->json($job);
});

Route::get('print_receipt_test', function(Request $request){
    $receipt = (new ReceiptPrinter);
    $receipt->text('VANTAGE POINT ENTERPRISES LIMITED');
    //$receipt->leftAlign();
    $receipt->feed();
    
    /* Name of tenant */
    $receipt->text("Name: ". "John Doe");
    $receipt->text("House: A401". "123");
    $receipt->text("Date: ". date("j F, Y", strtotime("2023-10-01")));
    $receipt->feed();

    $receipt->cut();
    
    //return response()->json([
        //"result" => true,
        //"content" => (string)$receipt
    //]);

    $job = PrintJob::create([
        'device_id' => 1,
        'printer_id' => 8,
        'type' => 'raw',
        'content' => (string)$receipt,
        'status' => 'pending',
        'attempts' => 0,
        'idempotency_key' => uniqid('job_')
    ]);

    $job->load(['printer', 'device']);

    broadcast(new PrintJobCreated($job));

    return response()->json($job);
});

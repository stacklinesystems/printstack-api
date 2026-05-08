<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\Request;

class PrintJobController extends Controller
{
    public function fetch(Request $request)
    {
        return PrintJob::where('client_id', $request->client->id)
            ->where('status', 'pending')
            ->limit(10)
            ->get();
    }

    public function updateStatus(Request $request, PrintJob $printJob)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $status = $request->status;

        $allowed = [
            'received',
            'processing',
            'printed',
            'acknowledged',
            'failed'
        ];

        if (!in_array($status, $allowed)) {

            return response()->json([
                'success' => false
            ], 422);
        }

        $data = [
            'status' => $status,
            'attempts' => $printJob->attempts + 1
        ];

        switch ($status) {

            case 'received':
                $data['received_at'] = now();
                break;

            case 'processing':
                $data['processing_at'] = now();
                break;

            case 'printed':
                $data['printed_at'] = now();
                break;

            case 'acknowledged':
                $data['acknowledged_at'] = now();
                break;
        }

        if ($status === 'failed') {
            $data['failure_reason'] =
                $request->failure_reason;
        }

        $printJob->update($data);

        return response()->json([
            'success' => true,
            'job_id' => $printJob->id,
            'status' => $printJob->status
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Events\PrintJobCreated;
use App\Models\PrintJob;
use Illuminate\Http\Request;

class PrintJobController extends Controller
{
    public function fetch(Request $request)
    {
        return PrintJob::where('client_id', $request->device->id)
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'printer_id' => 'nullable|exists:printers,id',
            'type' => 'required|in:raw,pdf,label,tspl,escpos',
            'content' => 'required|string',
            'idempotency_key' => 'nullable|string|max:255',
        ]);

        // 🔐 Prevent duplicate jobs (VERY IMPORTANT for POS systems)
        if (!empty($validated['idempotency_key'])) {

            $existing = PrintJob::where('idempotency_key', $validated['idempotency_key'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Duplicate ignored',
                    'job' => $existing
                ]);
            }
        }

        $job = PrintJob::create([
            'device_id' => $validated['device_id'],
            'printer_id' => $validated['printer_id'] ?? null,
            'type' => $validated['type'],
            'content' => $validated['content'],
            'status' => 'queued',
            'idempotency_key' => $validated['idempotency_key'] ?? uniqid('job_'),
        ]);

        $job->load(['printer', 'device']);

        $host = $job->device->name; // or printer hostname

        broadcast(new PrintJobCreated($job, $host));

        return response()->json([
            'success' => true,
            'job' => $job
        ]);
    }
}

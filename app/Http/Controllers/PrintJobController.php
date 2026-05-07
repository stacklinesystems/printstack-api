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

    public function updateStatus(Request $request, $id)
    {
        $job = PrintJob::findOrFail($id);

        if ($job->status === 'done') {
            return response()->json(['error' => 'Job already completed'], 409);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,done,failed'
        ]);

        $job->update([
            'status' => $request->status,
            'attempts' => $job->attempts + 1
        ]);

        return response()->json([
            'success' => true,
            'job_id' => $job->id,
            'status' => $job->status
        ]);
    }
}

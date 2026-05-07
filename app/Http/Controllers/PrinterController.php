<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PrinterController extends Controller
{
    public function index(Request $request)
    {
        return $request->client->printers;
    }

    public function store(Request $request)
    {
        $deviceName = $request->header('X-Device-Name');
        $user = Auth::user();

        $device = Device::firstOrCreate(
            [
                'name' => $deviceName,
                'user_id' => $user->id
            ],
            [
                'is_online' => true,
                'last_seen' => now()
            ]
        );

        $data = Printer::updateOrCreate(
            [
                'device_id' => $device->id,
                'name' => $request->name
            ],
            [
                'is_default' => $request->is_default ?? false,
                'is_active' => $request->is_active ?? true,
                'is_online' => $request->is_online ?? 'unknown'
            ]
        );

        broadcast(new \App\Events\PrintersUpdated($data));
        return $data;
    }

    public function update(Request $request, $id)
    {
        $printer = Printer::findOrFail($id);

        $printer->update([
            'is_default' => $request->is_default,
            'is_online' => $request->is_online,
        ]);

        broadcast(new \App\Events\PrintersUpdated($printer));
        return response()->json($printer);
    }
}

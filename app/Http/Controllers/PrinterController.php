<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrinterController extends Controller
{
    public function index(Request $request)
    {
        $deviceName = $request->header('X-Device-Name');

        $device = Device::where('name', $deviceName)->first();

        if (!$device) {
            return response()->json([
                'printers' => []
            ]);
        }

        return response()->json([
            'printers' => Printer::where('device_id', $device->id)->get()
        ]);
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
                'fingerprint' => $request->fingerprint,
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

    public function sync(Request $request)
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

        foreach ($request->printers as $printer) {

            Printer::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'name' => $printer->name
                ],
                [
                    'fingerprint' => $printer->fingerprint,
                    'is_default' => $printer->is_default ?? false,
                    'is_active' => $printer->is_active ?? true,
                    'is_online' => $printer->is_online ?? 'unknown'
                ]
            );
        }

        return response()->json([
            'success' => true
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Notifications\CriticalIncidentReported;
use Illuminate\Support\Facades\Notification;

class IncidentController extends Controller
{
    // Report an Incident on trip
    public function store(Request $request, Trip $trip): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:breakdown,accident,delay,fuel_theft,other'],
            'severity' => ['required', 'string', 'in:low,medium,high,critical'],
            'description' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'estimated_delay_hours' => ['nullable', 'numeric', 'min:0', 'max:72'],
        ]);

        $incident = $trip->incidents()->create([
            'reported_by' => $request->user()->id,
            'type' => $validated['type'],
            'severity' => $validated['severity'],
            'description' => $validated['description'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'estimated_delay_hours' => $validated['estimated_delay_hours'] ?? 0,
            'status' => 'open',
        ]);

        if (in_array($incident->severity, ['high', 'critical'])) {
            $admins = User::role('admin')->get();
            Notification::send($admins, new CriticalIncidentReported($incident));
        }

        return response()->json([
            'message' => 'Incident reported successfully',
            'data' => $incident->load('reporter:id,name,email'),
        ], 201);
    }

    public function resolve(Incident $incident): JsonResponse
    {
        $incident->update(['status' => 'resolved']);

        return response()->json([
            'message' => 'Incident marked as resolved',
            'data' => $incident,
        ]);
    }
}

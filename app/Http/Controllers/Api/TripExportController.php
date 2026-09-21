<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\TripStatus;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Trip;

class TripExportController extends Controller
{
    public function exportCsv(Request $request): StreamedResponse
    {
        $fileName = 'fleet_trips_expenses_' . now()->format('Y-m-d_His') . '.csv';

        $query = Trip::with(['vehicle', 'driver'])
            ->where('status', TripStatus::COMPLETED)
            ->filter($request->only(['from_date', 'to_date', 'vehicle_id', 'driver_id']))
            ->latest('completed_at');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            //1. CSV Headers Row
            fputcsv($handle, [
                'Trip Number',
                'Vehicle Plate',
                'vehicle Model',
                'Driver Name',
                'Driver Phone',
                'Origin',
                'Destination',
                'Start Odometer (KM)',
                'End Odometer (KM)',
                'Toal Distance (KM)',
                'Fuel Rate (PKR)',
                'Fuel Cost (PKR)',
                'Toll Tax (PKR)',
                'Misc Expenses (PKR)',
                'Total Trip Cost (PKR)',
                'Started At',
                'Completed At',
            ]);

            $query->chunk(200, function ($trips) use ($handle) {
                foreach ($trips as $trip) {
                    $distance = ($trip->end_odometer && $trip->start_odometer) ? ($trip->end_odometer - $trip->start_odometer): 0;

                    fputcsv($handle, [
                        $trip->trip_number,
                        $trip->vehicle?->plate_number ?? 'N/A',
                        $trip->vehicle ? "{$trip->vehicle->make} {$trip->vehicle->model}" : 'N/A',
                        $trip->driver?->name ?? 'N/A',
                        $trip->driver?->phone ?? 'N/A',
                        $trip->origin,
                        $trip->destination,
                        $trip->start_odometer,
                        $trip->end_odometer,
                        $distance,
                        $trip->fuel_rate_per_liter,
                        $trip->fuel_cost,
                        $trip->toll_tax,
                        $trip->misc_expenses,
                        $trip->total_cost,
                        $trip->started_at?->format('Y-m-d H:i:s'),
                        $trip->completed_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($handle);
        }, 200, $headers);
    }
}

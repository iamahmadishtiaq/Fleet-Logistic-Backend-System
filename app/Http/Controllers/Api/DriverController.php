<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    // GET /api/drivers
    public function index(Request $request): AnonymousResourceCollection
    {
        $drivers = Driver::query()
            ->when($request->query('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        return DriverResource::collection($drivers);
    }

    // POST /api/drivers
    public function store(StoreDriverRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Agar file upload aayi ho toh storage/app/public/drivers/licenses mein save karein
        if ($request->hasFile('license_document')) {
            $data['license_document'] = $request->file('license_document')
                ->store('drivers/licenses', 'public');
        }

        $driver = Driver::create($data);

        return response()->json([
            'message' => 'Driver registered successfully.',
            'data' => new DriverResource($driver),
        ], 201);
    }

    // GET /api/drivers/{driver}
    public function show(Driver $driver): JsonResponse
    {
        return response()->json([
            'data' => new DriverResource($driver),
        ]);
    }

    // POST/PUT /api/drivers/{driver}
    public function update(UpdateDriverRequest $request, Driver $driver): JsonResponse
    {
        $data = $request->validated();

        // Agar nayi file aayi ho toh purani file delete karke nayi store karein
        if ($request->hasFile('license_document')) {
            if ($driver->license_document && Storage::disk('public')->exists($driver->license_document)) {
                Storage::disk('public')->delete($driver->license_document);
            }

            $data['license_document'] = $request->file('license_document')
                ->store('drivers/licenses', 'public');
        }

        $driver->update($data);

        return response()->json([
            'message' => 'Driver updated successfully.',
            'data' => new DriverResource($driver),
        ]);
    }

    // DELETE /api/drivers/{driver}
    public function destroy(Driver $driver): JsonResponse
    {
        // Attached file delete karein
        if ($driver->license_document && Storage::disk('public')->exists($driver->license_document)) {
            Storage::disk('public')->delete($driver->license_document);
        }

        $driver->delete();

        return response()->json([
            'message' => 'Driver record deleted successfully.',
        ]);
    }
}
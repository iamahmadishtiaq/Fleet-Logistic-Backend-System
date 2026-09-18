<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;


class CheckFleetMaintenanceAlerts extends Command
{

    protected $signature = 'fleet:check-maintenance {--threshold=9000 : KM distance since last service to warn}';

    protected $description = 'Scan fleet vehicles for overdue maintenance and expiring fitness certificates';
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $this->info("Scanning fleet for vehicles exceeding {$threshold} KM since last service....");

        $vehicles = Vehicle::all();
        $overdueCount = 0;
        $fitnessExpiringCount = 0;

        foreach ($vehicles as $vehicle) {
            $kmSinceService = $vehicle->odometer - $vehicle->last_service_odometer;

            // 1. Check mileage alert
            if ($kmSinceService >= 10000) {
                $overdueCount++;
                $this->error("CRITICAL: Vehicle  [{$vehicle->plate_number}] is OVERDUE for service by {$kmSinceService} KM.");

                if ($vehicle->status !== VehicleStatus::MAINTENANCE) {
                    $vehicle->update(['status' => VehicleStatus::MAINTENANCE]);
                } elseif ($kmSinceService >= $threshold) {
                    $this->warn("WARNING: Vehicle [{$vehicle->plate_number}] needs service soon ({$kmSinceService} KM since last service).");
                }
                // 2. Check fitness expiration (within 30 days)
                if ($vehicle->fitness_expires_at && $vehicle->fitness_expires_at->isPast()) {
                    $fitnessExpiringCount++;
                    $this->error("EXPIRED: Vehicle [{$vehicle->plate_number}] fitness expired on {$vehicle->fitness_expires_at->format('Y-m-d')}.");
                } elseif ($vehicle->fitness_expires_at && $vehicle->fitness_expires_at->diffInDays(now()) <= 30) {
                    $fitnessExpiringCount++;
                    $this->warn("EXPIRING: Vehicle [{$vehicle->plate_number}] fitness expires in {$vehicle->fitness_expires_at->diffInDays(now())} days.");
                }
            }

            $this->newLine();
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Vehicles Scanned', $vehicles->count()],
                    ['Service Critical / Overdue', $overdueCount],
                    ['Fitness Expired / Expiring', $fitnessExpiringCount],
                ],
            );
            Log::info("FLeet Maintenance audit completed. Overdue: {$overdueCount}, Fitness Issues: {$fitnessExpiringCount}");

            return Command::SUCCESS;
        }
    }
}

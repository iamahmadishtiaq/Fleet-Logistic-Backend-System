Fleet Logistics Backend System
A production-ready, high-performance RESTful API engineered with Laravel 11, designed to streamline enterprise logistics, fleet asset management, trip dispatching, automated maintenance tracking, and real-time GPS telemetry ingestion.

Key Features
1.RBAC & Granular Permissions: Role-based security powered by spatie/laravel-permission (Admin & Dispatcher roles).
2.Trip Lifecycle Management: Automated asset status transitions (available → on_trip → completed), odometer validation, and trip expense aggregation.
3.Automated Fleet Maintenance Auditing: CLI command (fleet:check-maintenance) with dynamic warning thresholds (default 9,000 KM) and fitness certificate tracking, integrated into the Laravel Scheduler.
4.Memory-Efficient Financial Exports: Downloadable CSV reporting for completed trips and operational costs using chunked StreamedResponse.
5.Real-Time GPS Telemetry: Sub-minute coordinate ping ingestion, route trail reconstructions, and a live map overview endpoint (/api/fleet/live-map).
6.Incident & SOS Alerting: Real-time breakdown/accident logging with automated critical alert emails triggered via Laravel Notifications.
7.API Throttling & Security: Tiered rate limiters separating brute-force auth protection from high-capacity GPS telemetry streams.
8.Automated Testing: Comprehensive feature tests verifying trip lifecycles, role permissions, and edge cases.

Tech Stack
Framework: Laravel 13.x
PHP Version: 8.2+
Authentication: Laravel Sanctum
Authorization: Spatie Laravel-Permission
Database: MySQL / PostgreSQL
Testing: PHPUnit / Pest

1.Installation & Setup
git clone https://github.com/your-username/fleet-logistics-backend.git
cd fleet-logistics-backend

2.Install Dependencies
composer install

3.Environment Configuration
Copy the .env.example file and configure your credentials:
cp .env.example .env
php artisan key:generate

Set up your database and mail settings in .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fleet_logistics
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_MAILER=log
MAIL_FROM_ADDRESS="alerts@fleetlogistics.com"
MAIL_FROM_NAME="Fleet Logistics Alert System"

4.Run Migrations & Seeders
Set up the database schema and initialize default roles, permissions, and test accounts:
php artisan migrate --seed

5.Testing
Run the automated feature test suite:
php artisan test

6.Console Commands & Automation
Maintenance Health Scan
Scan all fleet vehicles for overdue maintenance intervals (10,000 KM hard limit) or upcoming threshold warnings:
# Run with default threshold (9,000 KM)
php artisan fleet:check-maintenance

# Run with custom threshold
php artisan fleet:check-maintenance --threshold=8500

7.Scheduler Setup (Production Server)
To run automated audits daily at 08:00 AM, add the following entry to your Linux server crontab:
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

API Reference
Authentication
Method,Endpoint,Description,Access
POST,/api/login,Authenticate and obtain Bearer token,Public (Rate Limited)
POST,/api/logout,Revoke current token,Authenticated
GET,/api/me,Fetch authenticated user profile,Authenticated

Fleet Assets & Drivers
Method,Endpoint,Description,Access
GET,/api/vehicles,List all vehicles,Authenticated
POST,/api/vehicles,Register a new vehicle,Admin (manage-vehicles)
DELETE,/api/vehicles/{id},Decommission vehicle,Admin (manage-vehicles)
POST,/api/vehicles/{id}/complete-service,Reset odometer after maintenance,Admin
GET,/api/drivers,List all drivers,Authenticated

Trips & Live Operations
Method,Endpoint,Description,Access
POST,/api/trips,Dispatch a new trip,"Dispatcher, Admin"
PUT,/api/trips/{id},Complete trip & update odometer,"Dispatcher, Admin"
POST,/api/trips/{id}/location,Ingest real-time GPS telemetry,Authenticated (Rate Limited)
GET,/api/trips/{id}/trail,Fetch full GPS trail history,Authenticated
GET,/api/fleet/live-map,Combined map coordinates for active trips,Authenticated
POST,/api/trips/{id}/incidents,Log an emergency/breakdown,Authenticated
PATCH,/api/incidents/{id}/resolve,Mark incident as resolved,"Dispatcher, Admin"

Reports & Metrics
Method,Endpoint,Description,Access
GET,/api/dashboard/stats,High-level fleet KPIs,"Admin, Dispatcher"
GET,/api/trips/export/csv,Stream completed trip expenses as CSV,Admin (view-financials)

License
This project is open-source software licensed under the MIT License.

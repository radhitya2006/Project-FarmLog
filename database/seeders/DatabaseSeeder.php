<?php

namespace Database\Seeders;

use App\Enums\ActivityStatus;
use App\Enums\HarvestUnit;
use App\Enums\PlantingSeasonStatus;
use App\Enums\UserRole;
use App\Models\User;
use App\Support\EmailNormalizer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = EmailNormalizer::normalize((string) config('farmlog.admin_email'));
        $adminPassword = config('farmlog.admin_password');
        $isDemoEnvironment = app()->environment(['local', 'testing']);

        if (! $isDemoEnvironment && blank($adminPassword)) {
            throw new RuntimeException('Set FARMLOG_ADMIN_PASSWORD before seeding outside local/testing.');
        }

        $newPasswords = DB::transaction(function () use ($adminEmail, $adminPassword, $isDemoEnvironment): array {
            $credentials = [];
            $admin = User::query()->where('email', $adminEmail)->first();

            if ($admin === null) {
                $password = filled($adminPassword) ? (string) $adminPassword : Str::password(16);
                $admin = new User;
                $admin->name = 'Admin FarmLog';
                $admin->email = $adminEmail;
                $admin->password = $password;
                $admin->role = UserRole::Admin;
                $admin->is_active = true;
                $admin->save();
                $credentials[$adminEmail] = $password;
            } elseif ($admin->role !== UserRole::Admin) {
                throw new RuntimeException('Admin email is already used by a farmer account.');
            }

            if ($isDemoEnvironment) {
                $demoEmail = 'petani@farmlog.test';
                $farmer = User::query()->where('email', $demoEmail)->first();

                if ($farmer === null) {
                    $password = Str::password(16);
                    $farmer = new User;
                    $farmer->name = 'Petani Demo';
                    $farmer->email = $demoEmail;
                    $farmer->password = $password;
                    $farmer->role = UserRole::Farmer;
                    $farmer->is_active = true;
                    $farmer->save();
                    $credentials[$demoEmail] = $password;
                } elseif ($farmer->role !== UserRole::Farmer) {
                    throw new RuntimeException('Demo email is already used by an admin account.');
                }

                $land = $farmer->lands()->firstOrCreate(
                    ['name' => 'Lahan Utara'],
                    ['area_hectares' => '1.50', 'location' => 'Desa Contoh, Bali', 'notes' => 'Data demonstrasi.'],
                );

                $season = $land->plantingSeasons()->firstOrCreate(
                    ['crop_name' => 'Cabai', 'planted_at' => '2026-08-01'],
                    ['expected_harvest_at' => '2026-11-30', 'status' => PlantingSeasonStatus::Active],
                );

                $season->activities()->firstOrCreate(
                    ['name' => 'Penanaman', 'scheduled_for' => '2026-08-01'],
                    ['status' => ActivityStatus::Finished],
                );

                $season->activities()->firstOrCreate(
                    ['name' => 'Pemupukan', 'scheduled_for' => '2026-09-01'],
                    ['status' => ActivityStatus::Planned],
                );

                $season->harvests()->firstOrCreate(
                    ['harvested_at' => '2026-10-15', 'unit' => HarvestUnit::Kilogram->value],
                    ['quantity' => '125.50', 'notes' => 'Panen pertama.'],
                );
            }

            return $credentials;
        });

        foreach ($newPasswords as $email => $password) {
            $this->command?->info("Kredensial awal {$email}: {$password} (simpan sekarang; tidak ditampilkan ulang)");
        }
    }
}

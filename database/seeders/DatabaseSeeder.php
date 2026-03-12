<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'superadmin',
            'email' => 'admin@example.com',
        ]);

        $role = Role::firstOrCreate(['name' => 'superadmin']);

        $admin->assignRole($role);

        $path = Storage::disk('public')->path('domains.csv');

        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->command->error("File domains.csv tidak ditemukan di storage/app/public/");
            return;
        }

        Domain::truncate(); // ✅ Bersihkan dulu sebelum insert ulang

        $buffer = [];
        $chunkSize = 100;
        $isFirstRow = true;
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            if ($isFirstRow) {
                $isFirstRow = false;
                continue;
            }

            $buffer[] = [
                'id' => $row[0],
                'name' => $row[1],
                'zone' => $row[2],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($buffer) >= $chunkSize) {
                Domain::insertOrIgnore($buffer); // ✅ Skip jika ada duplikat di CSV
                $buffer = [];
            }
        }

        if (!empty($buffer)) {
            Domain::insertOrIgnore($buffer);
        }

        fclose($handle);
    }
}
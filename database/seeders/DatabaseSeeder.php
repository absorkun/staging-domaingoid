<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const DOMAIN_INSERT_CHUNK_SIZE = 1000;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'superadmin',
            'password' => bcrypt('password'),
        ]);

        $role = Role::firstOrCreate(['name' => 'superadmin']);

        $admin->assignRole($role);

        $path = Storage::disk('public')->path('domains.csv');
        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->command->error('File domains.csv tidak ditemukan di storage/app/public/');

            return;
        }

        $buffer = [];
        $isFirstRow = true;
        $now = now()->toDateTimeString();
        $insertedRows = 0;
        $skippedRows = 0;

        DB::table('domains')->truncate();

        while (($row = fgetcsv($handle)) !== false) {
            if ($isFirstRow) {
                $isFirstRow = false;

                continue;
            }

            if (count($row) < 3) {
                $skippedRows++;

                continue;
            }

            $id = filter_var($row[0], FILTER_VALIDATE_INT);
            $name = trim((string) $row[1]);
            $zone = trim((string) $row[2]);

            if ($id === false || $name === '' || $zone === '') {
                $skippedRows++;

                continue;
            }

            $buffer[] = [
                'id' => $id,
                'name' => $name,
                'zone' => $zone,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($buffer) >= self::DOMAIN_INSERT_CHUNK_SIZE) {
                DB::table('domains')->insertOrIgnore($buffer);
                $insertedRows += count($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table('domains')->insertOrIgnore($buffer);
            $insertedRows += count($buffer);
        }

        fclose($handle);

        $this->command?->info("Seed domains selesai. Diproses: {$insertedRows} baris, dilewati: {$skippedRows} baris.");
    }
}

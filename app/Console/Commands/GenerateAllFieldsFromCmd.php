<?php

namespace App\Console\Commands;

use App\Jobs\GenerateDNSSEC;
use App\Jobs\GenerateIpAddress;
use App\Jobs\GenerateNameserver;
use App\Jobs\GenerateStatusCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class GenerateAllFieldsFromCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for generate all fields value';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chain = array_values(array_filter([
            GenerateNameserver::makeBatch(),
            GenerateDNSSEC::makeBatch(),
            GenerateIpAddress::makeBatch(),
            GenerateStatusCode::makeBatch(),
        ]));

        if ($chain === []) {
            $this->warn('Tidak ada domain untuk diproses.');

            return self::SUCCESS;
        }

        Bus::chain($chain)->dispatch();

        $this->info('Seluruh batch berhasil didaftarkan ke queue.');

        return self::SUCCESS;
    }
}

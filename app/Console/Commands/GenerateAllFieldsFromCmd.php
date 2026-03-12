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
    public function handle()
    {
        Bus::chain([
            new GenerateNameserver(),
            new GenerateDNSSEC(),
            new GenerateIpAddress(),
            new GenerateStatusCode(),
        ])->dispatch();
    }
}

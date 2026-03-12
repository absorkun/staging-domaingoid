<?php

namespace App\Console\Commands;

use App\Jobs\GenerateNameserver;
use Illuminate\Console\Command;

class GenerateNameserverFromCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for generate Nameservers value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Apakah anda sudah menjalankan php artisan queue:work?', true)) {
            GenerateNameserver::dispatch();
            $this->info('Pantau log dari queue untuk informasi pengerjaan');
        } else {
            $this->error('Jalankan php artisan queue:work terlebih dahulu');
        }
    }
}

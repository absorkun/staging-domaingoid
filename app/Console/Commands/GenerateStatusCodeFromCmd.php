<?php

namespace App\Console\Commands;

use App\Jobs\GenerateStatusCode;
use Illuminate\Console\Command;

class GenerateStatusCodeFromCmd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for generate status codes value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Apakah anda sudah menjalankan php artisan queue:work?', true)) {
            GenerateStatusCode::dispatch();
            $this->info('Pantau log dari queue untuk informasi pengerjaan');
        } else {
            $this->error('Jalankan php artisan queue:work terlebih dahulu');
        }
    }
}

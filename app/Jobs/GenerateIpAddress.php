<?php

namespace App\Jobs;

use App\Models\Domain;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateIpAddress implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Domain::query()
            ->select(['id', 'name', 'zone', 'ip_address', 'domain_name_server'])
            ->orderBy('id')
            ->chunk(100, function ($domains) {
                foreach ($domains as $domain) {
                    try {
                        $nameservers = $domain->domain_name_server;

                        if (empty($nameservers)) {
                            Log::warning('[IP Address Fetch] nameserver kosong, skip domain: ' . $domain->id);
                            return;
                        }

                        $ips = gethostbynamel($nameservers[0]);
                        if (!$ips) {
                            throw new Exception('IP Address not found');
                        }

                        $domain->update(['ip_address' => $ips[0]]);

                        Log::info('[IP Address Fetch] berhasil diperbarui');

                    } catch (Throwable $e) {
                        Log::error("[IP Address Fetch] " . $e->getMessage());
                    }
                }
            });
    }
}

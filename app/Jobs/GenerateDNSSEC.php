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

class GenerateDNSSEC implements ShouldQueue
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
        $baseUrl = 'https://rdap.pandi.id/rdap/domain';

        Domain::query()
            ->select(['id', 'name', 'zone', 'dns_sec'])
            ->orderBy('id')
            ->chunk(100, function ($domains) use ($baseUrl) {
                foreach ($domains as $domain) {
                    try {
                        $apiUrl = $baseUrl . '/' . $domain->hostname;
                        $response = Http::timeout(10)->get($apiUrl);

                        if (!$response->ok()) {
                            throw new Exception('Not response');
                        }

                        $data = $response->json();

                        if (empty($data) || !isset($data['secureDNS']['delegationSigned'])) {
                            Log::warning('[DNSSEC Fetch] data tidak lengkap, skip domain: ' . $domain->id);
                            return;
                        }

                        $dns_sec = (bool) $data['secureDNS']['delegationSigned'];

                        $domain->update(['dns_sec' => $dns_sec]);

                        Log::info('[DNSSEC Fetch] berhasil diperbarui');

                    } catch (Throwable $e) {
                        Log::error("[DNSSEC Fetch] " . $e->getMessage());
                    }
                }
            });
    }
}

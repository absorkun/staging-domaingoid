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

class GenerateNameserver implements ShouldQueue
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
            ->select(['id', 'name', 'zone', 'domain_name_server'])
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
                        $nameservers = $data['nameservers'] ?? null;

                        if (empty($nameservers)) {
                            throw new Exception('Error nameservers');
                        }

                        $ns = [];
                        foreach ($nameservers as $nameserver) {
                            if (!empty($nameserver['ldhName'])) {
                                $ns[] = $nameserver['ldhName'];
                            }
                        }

                        if (empty($ns)) {
                            throw new Exception('Tidak ada ldhName valid di nameservers');
                        }

                        $domain->update(['domain_name_server' => $ns]);

                        Log::info('[Nameserver Fetch] berhasil diperbarui');

                    } catch (Throwable $e) {
                        Log::error("[Nameserver Fetch] " . $e->getMessage());
                    }
                }
            });
    }
}

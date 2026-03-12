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

class GenerateStatusCode implements ShouldQueue
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
            ->select(['id', 'name', 'zone'])
            ->orderBy('id')
            ->chunkById(100, function ($domains) {
                foreach ($domains as $domain) {
                    try {
                        if (empty($domain->hostname)) {
                            Log::warning('[Status Code Fetch] hostname kosong, skip domain: ' . $domain->id);
                            return;
                        }

                        $response = Http::timeout(10)
                            ->head("http://{$domain->hostname}");

                        $domain->update([
                            'status_code' => $response->status(),
                        ]);

                        Log::info("[Status Code Fetch] {$domain->hostname} : " . $response->status()); // ✅ Log sukses
    
                    } catch (Throwable $e) {
                        Log::error("[Status Code Fetch] {$domain->hostname} : " . $e->getMessage());
                    }
                }
            });
    }
}

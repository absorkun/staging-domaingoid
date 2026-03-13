<?php

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessStatusCodeChunk extends AbstractProcessDomainChunk
{
    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $domains = $this->domains(['id', 'name', 'zone', 'status_code']);

        if ($domains->isEmpty()) {
            return;
        }

        $responses = Http::pool(
            fn (Pool $pool) => $domains
                ->map(fn (Domain $domain) => $pool
                    ->as((string) $domain->id)
                    ->connectTimeout(5)
                    ->timeout(10)
                    ->head('http://'.$domain->hostname))
                ->all(),
            concurrency: 10,
        );

        $updated = 0;
        $skipped = 0;

        foreach ($domains as $domain) {
            $response = $responses[$domain->id] ?? null;

            if (! $response instanceof Response) {
                $skipped++;

                continue;
            }

            $statusCode = $response->status();

            if ($statusCode <= 0 || $domain->status_code === $statusCode) {
                if ($statusCode <= 0) {
                    $skipped++;
                }

                continue;
            }

            Domain::query()
                ->whereKey($domain->id)
                ->update(['status_code' => $statusCode]);

            $updated++;
        }

        Log::info('[Status Code Fetch] chunk processed', [
            'start_id' => $this->startId,
            'end_id' => $this->endId,
            'processed' => $domains->count(),
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }
}

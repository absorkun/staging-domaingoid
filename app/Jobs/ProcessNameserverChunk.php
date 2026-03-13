<?php

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessNameserverChunk extends AbstractProcessDomainChunk
{
    private const BASE_URL = 'https://rdap.pandi.id/rdap/domain';

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $domains = $this->domains(['id', 'name', 'zone', 'domain_name_server']);

        if ($domains->isEmpty()) {
            return;
        }

        $responses = Http::pool(
            fn (Pool $pool) => $domains
                ->map(fn (Domain $domain) => $pool
                    ->as((string) $domain->id)
                    ->acceptJson()
                    ->connectTimeout(5)
                    ->timeout(10)
                    ->get(self::BASE_URL.'/'.$domain->hostname))
                ->all(),
            concurrency: 10,
        );

        $updated = 0;
        $skipped = 0;

        foreach ($domains as $domain) {
            $response = $responses[$domain->id] ?? null;

            if (! $response instanceof Response || ! $response->ok()) {
                $skipped++;

                continue;
            }

            $nameservers = collect($response->json('nameservers', []))
                ->pluck('ldhName')
                ->filter()
                ->values()
                ->all();

            if ($nameservers === []) {
                $skipped++;

                continue;
            }

            if ($domain->domain_name_server === $nameservers) {
                continue;
            }

            Domain::query()
                ->whereKey($domain->id)
                ->update(['domain_name_server' => $nameservers]);

            $updated++;
        }

        Log::info('[Nameserver Fetch] chunk processed', [
            'start_id' => $this->startId,
            'end_id' => $this->endId,
            'processed' => $domains->count(),
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }
}

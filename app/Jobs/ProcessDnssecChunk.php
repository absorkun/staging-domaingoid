<?php

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessDnssecChunk extends AbstractProcessDomainChunk
{
    private const BASE_URL = 'https://rdap.pandi.id/rdap/domain';

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $domains = $this->domains(['id', 'name', 'zone', 'dns_sec']);

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

            $delegationSigned = $response->json('secureDNS.delegationSigned');

            if (! is_bool($delegationSigned)) {
                $skipped++;

                continue;
            }

            if ($domain->dns_sec === $delegationSigned) {
                continue;
            }

            Domain::query()
                ->whereKey($domain->id)
                ->update(['dns_sec' => $delegationSigned]);

            $updated++;
        }

        Log::info('[DNSSEC Fetch] chunk processed', [
            'start_id' => $this->startId,
            'end_id' => $this->endId,
            'processed' => $domains->count(),
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }
}

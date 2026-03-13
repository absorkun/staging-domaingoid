<?php

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Support\Facades\Log;

class ProcessIpAddressChunk extends AbstractProcessDomainChunk
{
    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $domains = $this->domains(['id', 'domain_name_server', 'ip_address']);

        if ($domains->isEmpty()) {
            return;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($domains as $domain) {
            $nameservers = collect($domain->domain_name_server)
                ->filter(fn (mixed $value) => is_string($value) && $value !== '')
                ->values();

            $primaryNameserver = $nameservers->first();

            if (! is_string($primaryNameserver)) {
                $skipped++;

                continue;
            }

            $resolvedIpAddresses = gethostbynamel($primaryNameserver);

            if (! is_array($resolvedIpAddresses) || $resolvedIpAddresses === []) {
                $skipped++;

                continue;
            }

            $ipAddress = $resolvedIpAddresses[0];

            if ($domain->ip_address === $ipAddress) {
                continue;
            }

            Domain::query()
                ->whereKey($domain->id)
                ->update(['ip_address' => $ipAddress]);

            $updated++;
        }

        Log::info('[IP Address Fetch] chunk processed', [
            'start_id' => $this->startId,
            'end_id' => $this->endId,
            'processed' => $domains->count(),
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }
}

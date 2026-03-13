<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateDNSSEC extends AbstractGenerateDomainBatch
{
    protected static function batchName(): string
    {
        return 'Generate DNSSEC';
    }

    protected static function dispatchedLogMessage(): string
    {
        return '[DNSSEC Fetch] batch dispatched';
    }

    protected static function emptyLogMessage(): string
    {
        return '[DNSSEC Fetch] tidak ada domain untuk diproses';
    }

    protected static function makeChunkJob(int $startId, int $endId): ShouldQueue
    {
        return new ProcessDnssecChunk($startId, $endId);
    }
}

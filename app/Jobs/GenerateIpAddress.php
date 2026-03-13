<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateIpAddress extends AbstractGenerateDomainBatch
{
    protected static function batchName(): string
    {
        return 'Generate IP address';
    }

    protected static function dispatchedLogMessage(): string
    {
        return '[IP Address Fetch] batch dispatched';
    }

    protected static function emptyLogMessage(): string
    {
        return '[IP Address Fetch] tidak ada domain untuk diproses';
    }

    protected static function makeChunkJob(int $startId, int $endId): ShouldQueue
    {
        return new ProcessIpAddressChunk($startId, $endId);
    }
}

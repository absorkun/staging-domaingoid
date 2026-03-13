<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateNameserver extends AbstractGenerateDomainBatch
{
    protected static function batchName(): string
    {
        return 'Generate nameserver';
    }

    protected static function dispatchedLogMessage(): string
    {
        return '[Nameserver Fetch] batch dispatched';
    }

    protected static function emptyLogMessage(): string
    {
        return '[Nameserver Fetch] tidak ada domain untuk diproses';
    }

    protected static function makeChunkJob(int $startId, int $endId): ShouldQueue
    {
        return new ProcessNameserverChunk($startId, $endId);
    }
}

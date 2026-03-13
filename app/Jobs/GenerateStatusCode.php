<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateStatusCode extends AbstractGenerateDomainBatch
{
    protected static function batchName(): string
    {
        return 'Generate status code';
    }

    protected static function dispatchedLogMessage(): string
    {
        return '[Status Code Fetch] batch dispatched';
    }

    protected static function emptyLogMessage(): string
    {
        return '[Status Code Fetch] tidak ada domain untuk diproses';
    }

    protected static function makeChunkJob(int $startId, int $endId): ShouldQueue
    {
        return new ProcessStatusCodeChunk($startId, $endId);
    }
}

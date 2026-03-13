<?php

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

abstract class AbstractGenerateDomainBatch implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected const CHUNK_SIZE = 50;

    public int $tries = 1;

    public int $timeout = 30;

    public function handle(): void
    {
        $batch = static::makeBatch();

        if ($batch === null) {
            Log::info(static::emptyLogMessage());

            return;
        }

        $batch->dispatch();

        Log::info(static::dispatchedLogMessage(), [
            'jobs' => $batch->jobs->count(),
            'chunk_size' => static::CHUNK_SIZE,
        ]);
    }

    public static function makeBatch(): ?PendingBatch
    {
        $minimumId = Domain::query()->min('id');

        if ($minimumId === null) {
            return null;
        }

        $maximumId = (int) Domain::query()->max('id');
        $jobs = [];

        for ($startId = (int) $minimumId; $startId <= $maximumId; $startId += static::CHUNK_SIZE) {
            $jobs[] = static::makeChunkJob(
                $startId,
                min($startId + static::CHUNK_SIZE - 1, $maximumId),
            );
        }

        return Bus::batch($jobs)
            ->allowFailures()
            ->name(static::batchName());
    }

    abstract protected static function batchName(): string;

    abstract protected static function dispatchedLogMessage(): string;

    abstract protected static function emptyLogMessage(): string;

    abstract protected static function makeChunkJob(int $startId, int $endId): ShouldQueue;
}

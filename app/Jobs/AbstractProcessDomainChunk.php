<?php

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class AbstractProcessDomainChunk implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 1;

    public int $timeout = 85;

    public bool $failOnTimeout = true;

    /**
     * @var array<int, int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $startId,
        public int $endId,
    ) {}

    /**
     * @param  array<int, string>  $columns
     */
    protected function domains(array $columns): Collection
    {
        return $this->query()
            ->select($columns)
            ->orderBy('id')
            ->get();
    }

    protected function query(): Builder
    {
        return Domain::query()->whereBetween('id', [$this->startId, $this->endId]);
    }
}

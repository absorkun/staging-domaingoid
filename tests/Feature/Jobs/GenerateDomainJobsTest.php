<?php

use App\Jobs\GenerateNameserver;
use App\Jobs\ProcessNameserverChunk;
use App\Models\Domain;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('dispatches nameserver work in small queue batches', function () {
    Domain::query()->insert(
        collect(range(1, 120))
            ->map(fn (int $index) => [
                'name' => 'domain-'.$index,
                'zone' => '.id',
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all(),
    );

    Bus::fake();

    (new GenerateNameserver)->handle();

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->name === 'Generate nameserver'
            && $batch->jobs->count() === 3
            && $batch->jobs->every(fn (object $job) => $job instanceof ProcessNameserverChunk);
    });
});

it('updates nameservers for a chunk without stopping on partial failures', function () {
    $firstDomain = Domain::query()->create([
        'name' => 'alpha',
        'zone' => '.id',
    ]);

    $secondDomain = Domain::query()->create([
        'name' => 'beta',
        'zone' => '.id',
    ]);

    Http::fake([
        'https://rdap.pandi.id/rdap/domain/alpha.id' => Http::response([
            'nameservers' => [
                ['ldhName' => 'ns1.alpha.id'],
                ['ldhName' => 'ns2.alpha.id'],
            ],
        ]),
        'https://rdap.pandi.id/rdap/domain/beta.id' => Http::response([], 500),
    ]);

    (new ProcessNameserverChunk($firstDomain->id, $secondDomain->id))->handle();

    expect($firstDomain->fresh()->domain_name_server)->toBe(['ns1.alpha.id', 'ns2.alpha.id'])
        ->and($secondDomain->fresh()->domain_name_server)->toBeNull();

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://rdap.pandi.id/rdap/domain/alpha.id');
});

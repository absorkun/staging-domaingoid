<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DomainReportController extends Controller
{
    public function __invoke(): View
    {
        $domainQuery = Domain::query();

        $summary = [
            'total_domains' => (clone $domainQuery)->count(),
            'nameserver_ready' => (clone $domainQuery)->whereNotNull('domain_name_server')->count(),
            'dnssec_ready' => (clone $domainQuery)->whereNotNull('dns_sec')->count(),
            'ip_ready' => (clone $domainQuery)->whereNotNull('ip_address')->count(),
            'status_ready' => (clone $domainQuery)->whereNotNull('status_code')->count(),
        ];

        $latestBatches = DB::table('job_batches')
            ->select([
                'id',
                'name',
                'total_jobs',
                'pending_jobs',
                'failed_jobs',
                'created_at',
                'finished_at',
                'cancelled_at',
            ])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(function (object $batch): object {
                $batch->status = match (true) {
                    $batch->cancelled_at !== null => 'Cancelled',
                    $batch->pending_jobs > 0 => 'Running',
                    $batch->failed_jobs > 0 => 'Finished with failures',
                    $batch->finished_at !== null => 'Finished',
                    default => 'Queued',
                };

                $batch->created_at_human = Carbon::createFromTimestamp($batch->created_at)
                    ->timezone(config('app.timezone'))
                    ->format('d M Y H:i');

                $batch->finished_at_human = $batch->finished_at !== null
                    ? Carbon::createFromTimestamp($batch->finished_at)
                        ->timezone(config('app.timezone'))
                        ->format('d M Y H:i')
                    : '-';

                return $batch;
            });

        $domains = Domain::query()
            ->orderBy('id')
            ->paginate(25);

        return view('domains.index', [
            'domains' => $domains,
            'latestBatches' => $latestBatches,
            'summary' => $summary,
        ]);
    }
}

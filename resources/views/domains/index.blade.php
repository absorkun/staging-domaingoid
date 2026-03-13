<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }} | Domain Report</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <style>
            :root {
                color-scheme: light;
                --bg: #f4efe7;
                --panel: #fffdf9;
                --panel-strong: #fff8ef;
                --ink: #1f2937;
                --muted: #6b7280;
                --line: #e5d9c7;
                --accent: #9a3412;
                --accent-soft: #fed7aa;
                --good: #166534;
                --warn: #9a3412;
                --bad: #b91c1c;
                --shadow: 0 20px 45px rgba(57, 24, 0, 0.08);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: "Instrument Sans", sans-serif;
                background:
                    radial-gradient(circle at top left, rgba(251, 191, 36, 0.2), transparent 28rem),
                    linear-gradient(180deg, #fff7ed 0%, var(--bg) 42%, #efe7db 100%);
                color: var(--ink);
            }

            .shell {
                width: min(1200px, calc(100% - 2rem));
                margin: 0 auto;
                padding: 2rem 0 3rem;
            }

            .hero {
                display: grid;
                gap: 1rem;
                padding: 1.5rem;
                border: 1px solid rgba(154, 52, 18, 0.15);
                border-radius: 1.5rem;
                background: linear-gradient(135deg, rgba(255, 248, 239, 0.95), rgba(255, 255, 255, 0.86));
                box-shadow: var(--shadow);
            }

            .eyebrow {
                display: inline-flex;
                width: fit-content;
                padding: 0.35rem 0.7rem;
                border-radius: 999px;
                background: rgba(154, 52, 18, 0.08);
                color: var(--accent);
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }

            h1 {
                margin: 0;
                font-size: clamp(2rem, 4vw, 3.5rem);
                line-height: 0.95;
            }

            .subtle {
                margin: 0;
                max-width: 52rem;
                color: var(--muted);
                line-height: 1.6;
            }

            .grid {
                display: grid;
                gap: 1rem;
                margin-top: 1.5rem;
            }

            .stats {
                grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            }

            .stat,
            .panel {
                border: 1px solid var(--line);
                border-radius: 1.25rem;
                background: rgba(255, 253, 249, 0.92);
                box-shadow: var(--shadow);
            }

            .stat {
                padding: 1rem 1.1rem;
            }

            .stat-label {
                margin: 0;
                font-size: 0.8rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: var(--muted);
            }

            .stat-value {
                margin: 0.45rem 0 0;
                font-size: 2rem;
                font-weight: 700;
            }

            .content {
                grid-template-columns: 1.2fr 0.8fr;
                align-items: start;
            }

            .panel-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1.1rem 1.25rem;
                border-bottom: 1px solid var(--line);
            }

            .panel-title {
                margin: 0;
                font-size: 1.05rem;
                font-weight: 700;
            }

            .panel-note {
                margin: 0.2rem 0 0;
                color: var(--muted);
                font-size: 0.92rem;
            }

            .table-wrap {
                overflow-x: auto;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 0.9rem 1.25rem;
                border-bottom: 1px solid var(--line);
                text-align: left;
                vertical-align: top;
                font-size: 0.95rem;
            }

            th {
                font-size: 0.78rem;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: var(--muted);
                background: rgba(255, 248, 239, 0.68);
            }

            tr:last-child td {
                border-bottom: 0;
            }

            .mono {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            }

            .badge {
                display: inline-flex;
                align-items: center;
                padding: 0.3rem 0.65rem;
                border-radius: 999px;
                font-size: 0.8rem;
                font-weight: 700;
                white-space: nowrap;
            }

            .badge.good {
                color: var(--good);
                background: rgba(22, 101, 52, 0.1);
            }

            .badge.warn {
                color: var(--warn);
                background: rgba(154, 52, 18, 0.12);
            }

            .badge.bad {
                color: var(--bad);
                background: rgba(185, 28, 28, 0.1);
            }

            .badge.muted {
                color: var(--muted);
                background: rgba(107, 114, 128, 0.12);
            }

            .batch-list {
                display: grid;
                gap: 0.9rem;
                padding: 1rem;
            }

            .batch-item {
                display: grid;
                gap: 0.5rem;
                padding: 1rem;
                border: 1px solid var(--line);
                border-radius: 1rem;
                background: var(--panel-strong);
            }

            .batch-top {
                display: flex;
                align-items: start;
                justify-content: space-between;
                gap: 0.75rem;
            }

            .batch-name {
                margin: 0;
                font-weight: 700;
            }

            .batch-meta {
                margin: 0;
                color: var(--muted);
                font-size: 0.88rem;
                line-height: 1.5;
            }

            .pagination {
                padding: 1rem 1.25rem 1.25rem;
                border-top: 1px solid var(--line);
            }

            .pagination nav {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                align-items: center;
                justify-content: space-between;
            }

            .pagination svg {
                width: 1rem;
                height: 1rem;
            }

            .pagination a,
            .pagination span {
                border-radius: 999px;
            }

            .empty {
                padding: 1.25rem;
                color: var(--muted);
            }

            @media (max-width: 960px) {
                .content {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <main class="shell">
            <section class="hero">
                <span class="eyebrow">Domain Monitor</span>
                <div>
                    <h1>Daftar domain dan ringkasan generate.</h1>
                    <p class="subtle">
                        Halaman ini menampilkan data domain yang sudah masuk ke database, progres field hasil generate,
                        dan riwayat batch queue terakhir secara sederhana.
                    </p>
                </div>
            </section>

            <section class="grid stats">
                <article class="stat">
                    <p class="stat-label">Total Domain</p>
                    <p class="stat-value">{{ number_format($summary['total_domains']) }}</p>
                </article>
                <article class="stat">
                    <p class="stat-label">Nameserver Ready</p>
                    <p class="stat-value">{{ number_format($summary['nameserver_ready']) }}</p>
                </article>
                <article class="stat">
                    <p class="stat-label">DNSSEC Ready</p>
                    <p class="stat-value">{{ number_format($summary['dnssec_ready']) }}</p>
                </article>
                <article class="stat">
                    <p class="stat-label">IP Ready</p>
                    <p class="stat-value">{{ number_format($summary['ip_ready']) }}</p>
                </article>
                <article class="stat">
                    <p class="stat-label">Status Ready</p>
                    <p class="stat-value">{{ number_format($summary['status_ready']) }}</p>
                </article>
            </section>

            <section class="grid content">
                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2 class="panel-title">Domain Table</h2>
                            <p class="panel-note">Menampilkan {{ $domains->count() }} data pada halaman ini.</p>
                        </div>
                    </div>

                    @if ($domains->isEmpty())
                        <p class="empty">Belum ada domain di database.</p>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Domain</th>
                                        <th>Nameserver</th>
                                        <th>DNSSEC</th>
                                        <th>IP</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($domains as $domain)
                                        <tr>
                                            <td class="mono">{{ $domain->id }}</td>
                                            <td>
                                                <strong>{{ $domain->hostname }}</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $nameservers = collect($domain->domain_name_server)->filter()->values();
                                                @endphp

                                                @if ($nameservers->isEmpty())
                                                    <span class="badge muted">Belum ada</span>
                                                @else
                                                    <div class="mono">{{ $nameservers->take(2)->implode(', ') }}</div>
                                                    @if ($nameservers->count() > 2)
                                                        <div class="panel-note">+{{ $nameservers->count() - 2 }} lainnya</div>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if ($domain->dns_sec === null)
                                                    <span class="badge muted">Unknown</span>
                                                @elseif ($domain->dns_sec)
                                                    <span class="badge good">Enabled</span>
                                                @else
                                                    <span class="badge warn">Disabled</span>
                                                @endif
                                            </td>
                                            <td class="mono">
                                                {{ $domain->ip_address ?: '-' }}
                                            </td>
                                            <td>
                                                @if ($domain->status_code)
                                                    <span class="badge {{ $domain->status_code < 400 ? 'good' : 'bad' }}">
                                                        {{ $domain->status_code }}
                                                    </span>
                                                @else
                                                    <span class="badge muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination">
                            {{ $domains->onEachSide(1)->links() }}
                        </div>
                    @endif
                </article>

                <aside class="panel">
                    <div class="panel-head">
                        <div>
                            <h2 class="panel-title">Latest Generate Batches</h2>
                            <p class="panel-note">Ringkasan queue batch terakhir dari tabel `job_batches`.</p>
                        </div>
                    </div>

                    @if ($latestBatches->isEmpty())
                        <p class="empty">Belum ada histori batch generate.</p>
                    @else
                        <div class="batch-list">
                            @foreach ($latestBatches as $batch)
                                <article class="batch-item">
                                    <div class="batch-top">
                                        <div>
                                            <p class="batch-name">{{ $batch->name }}</p>
                                            <p class="batch-meta mono">{{ $batch->id }}</p>
                                        </div>
                                        <span class="badge
                                            @if ($batch->status === 'Finished')
                                                good
                                            @elseif ($batch->status === 'Finished with failures' || $batch->status === 'Running')
                                                warn
                                            @elseif ($batch->status === 'Cancelled')
                                                bad
                                            @else
                                                muted
                                            @endif
                                        ">
                                            {{ $batch->status }}
                                        </span>
                                    </div>

                                    <p class="batch-meta">
                                        Jobs: {{ number_format($batch->total_jobs) }} total,
                                        {{ number_format($batch->pending_jobs) }} pending,
                                        {{ number_format($batch->failed_jobs) }} failed
                                    </p>
                                    <p class="batch-meta">Created: {{ $batch->created_at_human }}</p>
                                    <p class="batch-meta">Finished: {{ $batch->finished_at_human }}</p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </aside>
            </section>
        </main>
    </body>
</html>

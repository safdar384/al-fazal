@php
    $fmt = fn ($n) => number_format((float) $n, 2);
    $fmtInt = fn ($n) => number_format((int) $n);
@endphp

@if ($report['groups']->isEmpty())
    <div class="bg-empty">No bilty / guds records found for the selected period.</div>
@else
    @foreach ($report['groups'] as $group)
        <section class="bg-block">
            <h2 class="bg-guds-title">
                <span class="bg-guds-name">{{ $group['guds_name'] }}</span>
                <span class="bg-guds-count">Total: {{ $fmtInt($group['totals']['invoice_count']) }}</span>
            </h2>

            @foreach ($group['invoices'] as $i => $inv)
                <article class="bg-entry">
                    <div class="bg-receiver">
                        <div class="bg-entry-num">{{ $i + 1 }}</div>
                        <div class="bg-receiver-body">
                            <div class="bg-receiver-label">Receiver address</div>
                            <div class="bg-receiver-address">{{ $inv->address_display }}</div>
                        </div>
                    </div>

                    <div class="bg-metrics">
                        <div class="bg-metric">
                            <span class="bg-metric-lbl">Corten</span>
                            <span class="bg-metric-val">{{ $fmtInt($inv->corten ?? 0) }}</span>
                        </div>
                        <div class="bg-metric">
                            <span class="bg-metric-lbl">Bilty Expense</span>
                            <span class="bg-metric-val">{{ $fmt($inv->biltyExpanse ?? 0) }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endforeach
@endif

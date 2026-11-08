@php
    $fmt = fn ($n) => number_format((float) $n, 2);
    $fmtInt = fn ($n) => number_format((int) $n);
@endphp

@if ($report['groups']->isEmpty())
    <div class="bg-empty">No bilty records found for the selected filters.</div>
@else
    @foreach ($report['groups'] as $group)
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center bg-guds-bar px-3 py-2">
                <strong class="text-uppercase">{{ $group['guds_name'] }}</strong>
                <span class="small fw-semibold">Total: {{ $fmtInt($group['totals']['invoice_count']) }}</span>
            </div>
            <div class="table-responsive bg-table-wrap">
                <table class="table table-sm table-hover align-middle mb-0 bg-data-table">
                    <thead>
                        <tr>
                            <th style="width:4%;">#</th>
                            <th style="width:9%;">Date</th>
                            <th style="width:11%;">Invoice No</th>
                            <th>Customer</th>
                            <th>Receiver Address</th>
                            <th style="width:8%;">Bilty No.</th>
                            <th class="text-end" style="width:7%;">Corten</th>
                            <th class="text-end" style="width:9%;">Bilty Exp.</th>
                            <th class="text-center" style="width:8%;">
                                <div class="d-flex flex-column align-items-center gap-1">
                                    <span>Delivered</span>
                                    <input type="checkbox" class="form-check-input bg-select-all" title="Select all in this guds">
                                </div>
                            </th>
                            <th style="width:12%;">Delivered Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($group['invoices'] as $i => $inv)
                            @php
                                $isDelivered = (bool) ($inv->is_delivered ?? $inv->delivered ?? false);
                                $deliveredAt = $inv->delivered_date
                                    ? \Carbon\Carbon::parse($inv->delivered_date)->format('d-M-Y h:i A')
                                    : '—';
                            @endphp
                            <tr class="{{ $isDelivered ? 'table-success-soft' : '' }}"
                                data-invoice="{{ $inv->invoiceNo }}"
                                data-delivered="{{ $isDelivered ? '1' : '0' }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($inv->invoiceDate)->format('d-M-Y') }}</td>
                                <td class="font-monospace fw-semibold">{{ $inv->invoiceNo }}</td>
                                <td>{{ $inv->name_display }}</td>
                                <td>{{ $inv->address_display }}</td>
                                <td>{{ $inv->biltyNo ?: '—' }}</td>
                                <td class="text-end">{{ $fmtInt($inv->corten ?? 0) }}</td>
                                <td class="text-end">{{ $fmt($inv->biltyExpanse ?? 0) }}</td>
                                <td class="text-center">
                                    <input type="checkbox"
                                           class="form-check-input bg-row-check"
                                           data-invoice="{{ $inv->invoiceNo }}"
                                           data-delivered="{{ $isDelivered ? '1' : '0' }}"
                                           title="Select row">
                                </td>
                                <td class="bg-delivered-date small">
                                    @if ($isDelivered)
                                        <span class="badge text-bg-success">Yes</span>
                                        <div class="mt-1">{{ $deliveredAt }}</div>
                                    @else
                                        <span class="badge text-bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end fw-bold">Guds Total</td>
                            <td class="text-end fw-bold">{{ $fmtInt($group['totals']['corten']) }}</td>
                            <td class="text-end fw-bold">{{ $fmt($group['totals']['bilty_expanse']) }}</td>
                            <td class="text-center fw-bold">{{ $fmtInt($group['totals']['delivered_count'] ?? 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach
@endif

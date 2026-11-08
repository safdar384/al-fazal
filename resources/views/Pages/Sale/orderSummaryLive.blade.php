@php
    $isPaginator = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator;
    $orderList = $isPaginator ? $orders->items() : $orders;
    $kpiMeta = [
        'Pending' => ['class' => 'pending', 'icon' => 'bi-hourglass-split'],
        'Confirmed' => ['class' => 'confirmed', 'icon' => 'bi-check2-circle'],
        'In Process' => ['class' => 'process', 'icon' => 'bi-arrow-repeat'],
        'Completed' => ['class' => 'completed', 'icon' => 'bi-check-circle'],
        'Cancelled' => ['class' => 'cancelled', 'icon' => 'bi-x-circle'],
    ];
    $filterQuery = request()->except(['fragment', 'page']);
@endphp

<div id="orderSummaryLive">
  <div class="row g-3 mb-3 no-print">
    @foreach($statusTotals as $box)
      @php $meta = $kpiMeta[$box['status']] ?? ['class' => '', 'icon' => 'bi-journal-text']; @endphp
      <div class="col-6 col-md-4 col-xl">
        <div class="ss-kpi {{ $meta['class'] }}">
          <div class="ss-kpi-icon {{ $meta['class'] }}"><i class="bi {{ $meta['icon'] }}"></i></div>
          <div>
            <div class="ss-kpi-lbl">{{ $box['status'] }} Orders</div>
            <div class="ss-kpi-val">{{ $box['total'] }}</div>
            <div class="ss-kpi-sub">Rs. {{ number_format($box['value'], 0) }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  <div class="card ss-card border-0">
    <div class="card-header ss-card-head">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h6 class="mb-0 text-white fw-semibold">
          <i class="bi bi-table me-2"></i>Orders
        </h6>
        <div class="d-flex flex-wrap align-items-center gap-2 no-print">
          <a href="{{ route('order.summary.print', $filterQuery) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light">
            <i class="bi bi-printer me-1"></i>Print Report
          </a>
          <a href="{{ route('order.index') }}" class="btn btn-sm btn-light fw-semibold">
            <i class="bi bi-plus-circle me-1"></i>New Order
          </a>
        </div>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 ss-table">
          <thead>
            <tr>
              <th class="text-center" style="width:4%">#</th>
              <th>Order No</th>
              <th>Invoice No</th>
              <th>Date</th>
              <th>Customer</th>
              <th class="text-center">Items</th>
              <th class="text-end">Amount</th>
              <th class="text-center">Status</th>
              <th class="text-center no-print">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($orderList as $idx => $order)
              <tr data-order-no="{{ $order->orderNo }}">
                <td class="text-center">{{ ($isPaginator ? $orders->firstItem() : 1) + $idx }}</td>
                <td class="fw-semibold font-monospace">{{ $order->orderNo }}</td>
                <td>{{ $order->converted_invoice_no ?: '—' }}</td>
                <td>{{ $order->orderDate?->format('d-M-Y') }}</td>
                <td>
                  {{ $order->customerName }}
                  @if($order->city)
                    <br><small class="text-muted">{{ $order->city }}</small>
                  @endif
                </td>
                <td class="text-center">{{ $order->noOfItems }}</td>
                <td class="text-end fw-semibold">Rs. {{ number_format((float) $order->grandTotal, 2) }}</td>
                <td class="text-center">
                  <span class="badge order-status-badge {{ $order->statusBadgeClass() }}">{{ $order->status }}</span>
                </td>
                <td class="text-center no-print order-actions">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('order.preview', $order->orderNo) }}" target="_blank" class="btn btn-outline-primary" title="View">
                      <i class="bi bi-eye"></i>
                    </a>
                    @if(!$order->isConverted() && $order->status !== 'Cancelled')
                      <a href="{{ route('order.edit', $order->orderNo) }}" class="btn btn-outline-secondary" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </a>
                    @endif
                    @if($order->canConvertToInvoice())
                      <button type="button" class="btn btn-outline-success btn-convert-invoice" data-order="{{ $order->orderNo }}" title="Convert to Invoice">
                        <i class="bi bi-receipt"></i>
                      </button>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">No orders found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($isPaginator && $orders->total() > 0)
        <div class="card-footer no-print d-flex flex-wrap justify-content-between align-items-center gap-2">
          <small class="text-muted">Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ $orders->total() }}</small>
          @if($orders->hasPages())
            {{ $orders->appends(request()->except('fragment'))->links('pagination::bootstrap-5') }}
          @endif
        </div>
      @endif
    </div>
  </div>
</div>

@php
    $orderRows = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator ? $orders->items() : $orders;
    $isPaginator = $orders instanceof \Illuminate\Pagination\LengthAwarePaginator;
@endphp
<div id="orderApprovalLive">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Order No</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th class="text-center">Items</th>
                    <th class="text-end">Amount</th>
                    <th class="text-center">Status</th>
                    <th class="text-center" style="width: 4.5rem;">
                        <input type="checkbox" class="form-check-input approval-check-all" title="Select all" aria-label="Select all orders">
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($orderRows as $i => $order)
                    <tr>
                        <td>{{ ($isPaginator ? $orders->firstItem() : 1) + $i }}</td>
                        <td><strong>{{ $order->orderNo }}</strong></td>
                        <td>{{ $order->orderDate ? date('d-M-Y', strtotime($order->orderDate)) : '-' }}</td>
                        <td>{{ $order->customerName }}</td>
                        <td class="text-center">{{ $order->noOfItems }}</td>
                        <td class="text-end fw-semibold">₨{{ number_format((float)$order->grandTotal, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $order->statusBadgeClass() }}">{{ $order->status }}</span>
                        </td>
                        <td class="text-center">
                            @if(!$order->isConverted())
                                <input type="checkbox" class="form-check-input approval-check" value="{{ $order->orderNo }}" aria-label="Select {{ $order->orderNo }}">
                            @else
                                <small class="text-muted">Invoiced</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No orders found.</td>
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

@extends('layout.app')

@section('title', 'Order Approval')

@section('content')
<style>
    #orderApprovalLive.is-loading { opacity: 0.45; pointer-events: none; }
    .approval-check, .approval-check-all { width: 1.15rem; height: 1.15rem; cursor: pointer; }
    .approval-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        color: #fff;
    }
    .approval-card-head .form-select { width: 11rem; background: #fff; }
</style>
<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h3 class="mb-0"><i class="bi bi-check2-square text-primary me-2"></i>Order Approval Section</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('order.summary') }}">Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Approval</li>
                    </ol>
                </div>
            </div>
            <hr class="mb-0" style="border-top: 2px solid #0d6efd;">
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card shadow-sm mb-3">
                <div class="card-header text-white py-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h6 class="mb-0"><i class="bi bi-funnel me-1"></i>Filters</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('order.approval') }}" id="orderApprovalFilter" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All open</option>
                                @foreach($statuses ?? config('orders.statuses', []) as $st)
                                    <option value="{{ $st }}" @selected(($status ?? '') === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">Area</label>
                            <select name="area" class="form-select form-select-sm">
                                <option value="">All Areas</option>
                                @foreach($areas as $a)
                                    <option value="{{ $a->areaCode }}" {{ ($area ?? '') === $a->areaCode ? 'selected' : '' }}>
                                        {{ $a->areaName }} ({{ $a->areaCode }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @include('partials.rowsPerPage', ['rowsId' => 'orderApprovalPerPage'])
                        <div class="col-md-auto d-flex gap-2">
                            <a href="{{ route('order.approval') }}" id="orderApprovalReset" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header approval-card-head d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-1"></i>Orders for Approval</h5>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small mb-0" for="approvalStatus">Apply status</label>
                        <select id="approvalStatus" class="form-select form-select-sm" aria-label="Status for checked orders">
                            <option value="">Select status</option>
                            @foreach($statuses ?? [] as $st)
                                <option value="{{ $st }}">{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @include('Pages.Sale.orderApprovalLive')
            </div>
        </div>
    </div>
</main>

<script>
(function () {
    const filterForm = document.getElementById('orderApprovalFilter');
    const statusSelect = document.getElementById('approvalStatus');
    if (!filterForm || !statusSelect) return;
    window.__orderApprovalAlive = true;
    let requestId = 0;
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    function loadList(params) {
        const id = ++requestId;
        params.delete('fragment');
        const clean = params.toString();
        const fetchParams = new URLSearchParams(clean);
        fetchParams.set('fragment', '1');
        const live = document.getElementById('orderApprovalLive');
        if (live) live.classList.add('is-loading');

        return fetch(filterForm.action + '?' + fetchParams.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        }).then(function (res) {
            if (!res.ok) throw new Error('load failed');
            return res.text();
        }).then(function (html) {
            if (id !== requestId) return;
            const current = document.getElementById('orderApprovalLive');
            if (!current) return;
            current.outerHTML = html.trim();
            history.replaceState(null, '', filterForm.action + (clean ? '?' + clean : ''));
        }).catch(function () {
            const current = document.getElementById('orderApprovalLive');
            if (current) current.classList.remove('is-loading');
            if (typeof showError === 'function') showError('Order Approval', 'Could not refresh the list.');
        });
    }

    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    filterForm.addEventListener('change', function (event) {
        if (!event.target.matches('select')) return;
        const params = new URLSearchParams(new FormData(filterForm));
        params.delete('page');
        loadList(params);
    });

    document.addEventListener('click', function (event) {
        const reset = event.target.closest('#orderApprovalReset');
        if (reset) {
            event.preventDefault();
            Array.from(filterForm.elements).forEach(function (field) {
                if (field.name && field.name !== 'per_page') field.value = '';
            });
            const statusField = filterForm.elements.status;
            if (statusField) statusField.value = 'Pending';
            if (filterForm.elements.per_page) filterForm.elements.per_page.value = '25';
            loadList(new URLSearchParams(new FormData(filterForm)));
            return;
        }

        const link = event.target.closest('#orderApprovalLive .pagination a');
        if (!link) return;
        const url = new URL(link.href, window.location.origin);
        if (url.pathname !== new URL(filterForm.action, window.location.origin).pathname) return;
        event.preventDefault();
        loadList(url.searchParams);
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('approval-check-all')) {
            document.querySelectorAll('.approval-check').forEach(function (box) {
                box.checked = event.target.checked;
            });
            return;
        }
        if (event.target.classList.contains('approval-check')) {
            const boxes = Array.from(document.querySelectorAll('.approval-check'));
            const all = document.querySelector('.approval-check-all');
            if (all) all.checked = boxes.length > 0 && boxes.every(function (box) { return box.checked; });
        }
    });

    statusSelect.addEventListener('change', function () {
        const status = statusSelect.value;
        if (!status) return;
        const orderNos = Array.from(document.querySelectorAll('.approval-check:checked')).map(function (box) {
            return box.value;
        });
        statusSelect.value = '';
        if (orderNos.length === 0) {
            if (typeof showWarning === 'function') showWarning('Select orders', 'Check the orders you want to update.');
            return;
        }

        statusSelect.disabled = true;
        fetch('{{ route('order.approval.apply') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: status, orderNos: orderNos })
        }).then(function (res) {
            return res.json().then(function (data) { return { ok: res.ok, data: data }; });
        }).then(function (result) {
            statusSelect.disabled = false;
            const message = result.data.message || 'Status updated.';
            if (result.ok && result.data.success) {
                if (typeof showSuccess === 'function') showSuccess('Updated', message, 2500);
                return loadList(new URLSearchParams(new FormData(filterForm)));
            }
            if (typeof showError === 'function') showError('Not updated', message, 3000);
        }).catch(function () {
            statusSelect.disabled = false;
            if (typeof showError === 'function') showError('Not updated', 'Could not update the selected orders.');
        });
    });
})();
</script>
@endsection


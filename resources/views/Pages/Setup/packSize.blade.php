@extends('layout.app')

@section('title', 'Manage Pack Size')

@section('content')
<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-7">
                    <h3 class="mb-0 fw-bold" style="color:#0f2747;">
                        <i class="bi bi-bounding-box me-2"></i>Manage Pack Size
                    </h3>
                    <p class="text-muted small mb-0">Packing + Unit (Piece, Dozen, Gurs, Kg, Feet) + Qty — one place for product packing</p>
                </div>
                <div class="col-sm-5">
                    <ol class="breadcrumb float-sm-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('product.index') }}">Products</a></li>
                        <li class="breadcrumb-item active">Pack Size</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header text-white py-3" style="background:#0f2747;">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-plus-circle me-1"></i> Add Pack Size</h6>
                        </div>
                        <div class="card-body">
                            <form id="packSizeForm" action="{{ route('packSize.store') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Packing <span class="text-danger">*</span></label>
                                    <select name="packing_type" id="psPacking" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach($packings as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Box, Bag, Pakit, Bundle</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                                    <select name="unit_name" id="psUnit" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        @foreach($units as $u)
                                            <option value="{{ $u }}">{{ $u }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="psUnitHint">Piece, Dozen, Kg, Gurs, Feet</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="qty" id="psQty" class="form-control" min="0.01" step="0.01" placeholder="e.g. 1, 12, 50" required>
                                    <small class="text-muted">Inside one pack (Dozen 1 = 12 pieces on product)</small>
                                </div>
                                <div class="mb-3 p-2 rounded border bg-light">
                                    <span class="text-muted small">Preview:</span>
                                    <div class="fw-bold" id="psPreview" style="color:#0f2747;">—</div>
                                </div>
                                <button type="submit" class="btn text-white w-100" style="background:#0f2747;">
                                    <i class="bi bi-save me-1"></i> Save Pack Size
                                </button>
                            </form>

                            <hr>
                            <p class="small text-muted mb-1 fw-semibold">Quick guide</p>
                            <ul class="small text-muted ps-3 mb-0">
                                <li>Box → Piece / Dozen</li>
                                <li>Bag → Kg</li>
                                <li>Pakit → Gurs</li>
                                <li>Bundle → Feet / Kg</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold"><i class="bi bi-list-ul me-1"></i> Pack Size List</h6>
                            <span class="badge text-bg-secondary">{{ $packSizes->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle" id="packSizeTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:8%;">#</th>
                                            <th>Label</th>
                                            <th>Packing</th>
                                            <th>Unit</th>
                                            <th class="text-end">Qty</th>
                                            <th class="text-center" style="width:12%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($packSizes as $i => $row)
                                            <tr data-id="{{ $row->id }}">
                                                <td>{{ $i + 1 }}</td>
                                                <td class="fw-semibold">{{ $row->label }}</td>
                                                <td>{{ $row->packing_type }}</td>
                                                <td>{{ $row->unit_name }}</td>
                                                <td class="text-end">{{ \App\Models\PackSize::formatQty($row->qty) }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ps" data-id="{{ $row->id }}" data-label="{{ $row->label }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="psEmptyRow">
                                                <td colspan="6" class="text-center text-muted py-4">No pack sizes yet. Add one on the left.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
    const suggested = @json($suggestedUnits);
    const $packing = $('#psPacking');
    const $unit = $('#psUnit');
    const $qty = $('#psQty');
    const $preview = $('#psPreview');
    const $hint = $('#psUnitHint');

    function formatQty(n) {
        n = parseFloat(n);
        if (isNaN(n)) return '';
        if (Math.abs(n - Math.round(n)) < 0.00001) return String(Math.round(n));
        return String(n);
    }

    function updatePreview() {
        const p = $packing.val();
        const u = $unit.val();
        const q = formatQty($qty.val());
        if (p && u && q) {
            $preview.text(p + ' · ' + q + ' ' + u);
        } else {
            $preview.text('—');
        }
    }

    function filterUnits() {
        const p = $packing.val();
        const list = suggested[p] || null;
        if (list) {
            $hint.text('Suggested for ' + p + ': ' + list.join(', ') + ' — other units also allowed');
        } else {
            $hint.text('Piece, Dozen, Kg, Gurs, Feet');
        }
        updatePreview();
    }

    $packing.on('change', filterUnits);
    $unit.on('change', updatePreview);
    $qty.on('input', updatePreview);

    $('#packSizeForm').on('submit', function (e) {
        e.preventDefault();
        const $f = $(this);
        const $btn = $f.find('button[type="submit"]').prop('disabled', true);
        $.ajax({
            url: $f.attr('action'),
            method: 'POST',
            data: $f.serialize(),
            dataType: 'json',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }).done(function (res) {
            const d = res.data;
            $('#psEmptyRow').remove();
            const n = $('#packSizeTable tbody tr').length + 1;
            const qtyLabel = formatQty(d.qty);
            const tr = `<tr data-id="${d.id}">
                <td>${n}</td>
                <td class="fw-semibold">${d.label}</td>
                <td>${d.packing_type}</td>
                <td>${d.unit_name}</td>
                <td class="text-end">${qtyLabel}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-ps" data-id="${d.id}" data-label="${d.label}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
            $('#packSizeTable tbody').append(tr);
            $f[0].reset();
            updatePreview();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Saved', text: res.message || 'Pack size saved.', confirmButtonColor: '#0f2747' });
            }
        }).fail(function (xhr) {
            const msg = (xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0]))) || 'Could not save.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#0f2747' });
            } else {
                alert(msg);
            }
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.btn-delete-ps', function () {
        const id = $(this).data('id');
        const label = $(this).data('label');
        const $row = $(this).closest('tr');
        const doDelete = function () {
            $.ajax({
                url: '{{ url('pack-size') }}/' + id,
                method: 'DELETE',
                dataType: 'json',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }).done(function (res) {
                $row.remove();
                if (!$('#packSizeTable tbody tr').length) {
                    $('#packSizeTable tbody').append('<tr id="psEmptyRow"><td colspan="6" class="text-center text-muted py-4">No pack sizes yet. Add one on the left.</td></tr>');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Deleted', text: res.message || 'Deleted.', confirmButtonColor: '#0f2747' });
                }
            }).fail(function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Cannot delete.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error', text: msg, confirmButtonColor: '#0f2747' });
                } else {
                    alert(msg);
                }
            });
        };
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Delete?',
                text: 'Remove "' + label + '"?',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                confirmButtonColor: '#dc3545'
            }).then(function (r) { if (r.isConfirmed) doDelete(); });
        } else if (confirm('Delete "' + label + '"?')) {
            doDelete();
        }
    });
})();
</script>
@endpush

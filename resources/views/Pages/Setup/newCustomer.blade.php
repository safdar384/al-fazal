@extends('layout.app')

@section('title', 'Add New Customer')

@section('content')

<main class="app-main" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center gy-2">
                <div class="col-12 col-md-6">
                    <h3 class="mb-0 fw-bold" style="color:#0f2747;">
                        <i class="bi bi-person-plus me-2"></i>Add New Customer
                    </h3>
                    <p class="text-muted small mb-0">Accounts · Create customer / supplier profile</p>
                </div>
                <div class="col-12 col-md-6">
                    <ol class="breadcrumb float-md-end mb-0">
                        <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('Customer.index') }}">Customers</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add New</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <div class="d-flex align-items-start">
                        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                        <div>
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('Customer.store') }}" method="POST" id="customerForm" class="nc-form"
                  autocomplete="off" data-lpignore="true" data-1p-ignore="true">
                        @csrf

                        <div class="row g-3">
                            <div class="col-lg-6">
                        <div class="card nc-card border-0 h-100">
                            <div class="card-header nc-card-head">
                                <h6 class="mb-0 text-white fw-semibold">
                                    <i class="bi bi-info-circle me-2"></i>Basic Information
                                        </h6>
                            </div>
                            <div class="card-body p-3">
                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Customer Area <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex gap-2">
                                                <select class="form-select select2 @error('custArea') is-invalid @enderror" 
                                                        name="custArea" 
                                                id="customerAreaSelect"
                                                autocomplete="off">
                                                    <option value="">-- Select Area --</option>
                                                    @foreach($custAreas as $custArea)
                                                <option value="{{ $custArea->areaCode }}" @selected(old('custArea') == $custArea->areaCode)>
                                                            {{ $custArea->areaCode }} - {{ $custArea->areaName }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                        <button type="button" class="btn btn-outline-secondary" onclick="openAreaModal()" title="Add New Area">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                            </div>
                                            @error('custArea')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Customer Code
                                        <span class="badge bg-success bg-opacity-10 text-success ms-1"><i class="bi bi-magic"></i> Auto</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custCode') is-invalid @enderror" 
                                                   name="custCode" 
                                                   value="{{ old('custCode') }}" 
                                                   placeholder="Leave blank for auto-generation"
                                                   autocomplete="off">
                                            @error('custCode')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                    <small class="form-text text-muted">Auto: CUST001, CUST002...</small>
                                        </div>

                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Business Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('bussnessName') is-invalid @enderror" 
                                                   name="bussnessName" 
                                                   value="{{ old('bussnessName') }}"
                                                   placeholder="Enter business name"
                                                   autocomplete="off" 
                                                   required>
                                            @error('bussnessName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Customer Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custName') is-invalid @enderror" 
                                                   name="custName" 
                                                   value="{{ old('custName') }}"
                                                   placeholder="Enter customer name"
                                                   autocomplete="off" 
                                           autofocus
                                                   required>
                                            @error('custName')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Customer Address <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control @error('custAddress') is-invalid @enderror" 
                                                      name="custAddress" 
                                                      rows="2"
                                                      placeholder="Enter complete address"
                                                      autocomplete="off" 
                                                      required>{{ old('custAddress') }}</textarea>
                                            @error('custAddress')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                <div class="row g-2">
                                            <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold nc-label">
                                                    City <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" 
                                                       class="form-control @error('custCity') is-invalid @enderror" 
                                                       name="custCity" 
                                                       value="{{ old('custCity') }}"
                                                       placeholder="Enter city"
                                                       autocomplete="off" 
                                                       required>
                                                @error('custCity')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold nc-label">
                                                    Contact <span class="text-danger">*</span>
                                                </label>
                                                    <input type="text" 
                                                           class="form-control @error('custContect') is-invalid @enderror" 
                                                           name="custContect" 
                                                           value="{{ old('custContect') }}"
                                                           placeholder="03XX-XXXXXXX"
                                                           autocomplete="off" 
                                                           required>
                                                    @error('custContect')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                            </div>
                                        </div>

                                        <div class="mb-0">
                                    <label class="form-label fw-semibold nc-label">
                                                Customer Page <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control @error('custPage') is-invalid @enderror" 
                                                   name="custPage" 
                                                   value="{{ old('custPage') }}"
                                                   placeholder="Enter page reference"
                                                   autocomplete="off" 
                                                   required>
                                            @error('custPage')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                        <div class="card nc-card border-0 mb-3">
                            <div class="card-header nc-card-head">
                                <h6 class="mb-0 text-white fw-semibold">
                                    <i class="bi bi-cash-stack me-2"></i>Financial Information
                                        </h6>
                            </div>
                            <div class="card-body p-3">
                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Discount (%) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control @error('custdiscount') is-invalid @enderror" 
                                                       name="custdiscount" 
                                                       value="{{ old('custdiscount', 0) }}"
                                                       placeholder="0.00"
                                                       autocomplete="off" 
                                                       required>
                                        <span class="input-group-text">%</span>
                                                @error('custdiscount')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Payment Term (Days) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control @error('custPayTermDay') is-invalid @enderror" 
                                                       name="custPayTermDay" 
                                                       value="{{ old('custPayTermDay', 0) }}"
                                                       placeholder="e.g., 30"
                                                       autocomplete="off" 
                                                       required>
                                        <span class="input-group-text">Days</span>
                                                @error('custPayTermDay')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Credit Limit <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                        <span class="input-group-text">Rs.</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control @error('custCreditLimit') is-invalid @enderror" 
                                                       name="custCreditLimit" 
                                                       value="{{ old('custCreditLimit', 0) }}"
                                                       placeholder="0.00"
                                                       autocomplete="off" 
                                                       required>
                                                @error('custCreditLimit')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                    <label class="form-label fw-semibold nc-label">
                                                Opening Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" 
                                                   class="form-control @error('custOppDate') is-invalid @enderror" 
                                                   name="custOppDate" 
                                                   value="{{ old('custOppDate', now()->format('Y-m-d')) }}"
                                                   autocomplete="off" 
                                                   required>
                                            @error('custOppDate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-0">
                                    <label class="form-label fw-semibold nc-label">
                                                Customer Category <span class="text-danger">*</span>
                                            </label>
                                                <select class="form-select @error('custCatagory') is-invalid @enderror"
                                                        name="custCatagory"
                                                        id="customerCatagorySelect"
                                            autocomplete="off"
                                                        required>
                                                    <option value="">-- Select Category --</option>
                                        @foreach($custCatagories ?? \App\Models\NewCustomer::CATEGORIES as $catagory)
                                            <option value="{{ $catagory }}"
                                                @selected(strtolower((string) old('custCatagory')) === strtolower($catagory))>
                                                {{ $catagory }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @error('custCatagory')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                    </div>
                                </div>
                            </div>

                        <div class="nc-tips">
                            <h6 class="fw-semibold mb-2" style="color:#0f2747;">
                                <i class="bi bi-lightbulb me-1"></i>Quick Tips
                                    </h6>
                            <ul class="mb-0 small ps-3 text-muted">
                                <li><strong>Customer Code:</strong> Leave blank for auto-generation</li>
                                <li><strong>Category:</strong> Retailers, WholeSale, Raw Material Supplier, Product Supplier, or Transport</li>
                                <li><strong>Payment Terms:</strong> Days allowed for payment</li>
                                        <li><strong>Credit Limit:</strong> Maximum credit allowed</li>
                                    </ul>
                            </div>
                        </div>

                    <div class="col-12">
                        <div class="card nc-card border-0">
                            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <small class="text-muted">
                                    <span class="text-danger fw-bold">*</span> Required fields
                                        </small>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('Customer.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-left me-1"></i>Back to List
                                    </a>
                                    <button type="reset" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                        </button>
                                    <button type="submit" class="btn btn-sm text-white px-4" style="background:#0f2747;border-color:#0f2747;">
                                        <i class="bi bi-save me-1"></i>Save Customer
                                        </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

{{-- Area Modal --}}
<div class="modal fade" id="areaModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header nc-modal-head">
                <h5 class="modal-title"><i class="bi bi-geo-alt-fill me-2"></i>Manage Areas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="areaForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold nc-label">Area Code <span class="text-danger">*</span></label>
                            <input type="text" name="areaCode" class="form-control" placeholder="e.g., A01" autocomplete="off" required>
                        <span class="text-danger error-areaCode"></span>
                    </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold nc-label">Area Name <span class="text-danger">*</span></label>
                            <input type="text" name="areaName" class="form-control" placeholder="Area name" autocomplete="off" required>
                        <span class="text-danger error-areaName"></span>  
                    </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold nc-label">Agent Name</label>
                        <select name="ajentName" class="form-select">
                            <option value="">-- Select Agent --</option>
                            @foreach($agentLabours ?? [] as $agentLabour)
                                <option value="{{ $agentLabour->labourName }}">
                                    {{ $agentLabour->labourCode }} - {{ $agentLabour->labourName }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-danger error-ajentName"></span>
                    </div>
                </div>

                    <div class="border-top pt-3">
                        <label class="form-label fw-semibold mb-2">Existing Areas</label>
                        <div class="table-responsive nc-area-table-wrap">
                            <table class="table table-sm table-hover align-middle mb-0" id="areaLookupTable">
                                <thead>
                                    <tr>
                                        <th style="width:18%;">Code</th>
                                        <th style="width:32%;">Area Name</th>
                                        <th style="width:35%;">Agent</th>
                                        <th style="width:15%;" class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="areaLookupBody">
                                    @forelse($custAreas ?? [] as $area)
                                        @php
                                            $isUsed = in_array(strtoupper(trim((string) $area->areaCode)), $usedAreaCodes ?? [], true);
                                        @endphp
                                        <tr class="area-lookup-item"
                                            data-id="{{ $area->id }}"
                                            data-code="{{ $area->areaCode }}"
                                            data-in-use="{{ $isUsed ? '1' : '0' }}">
                                            <td><strong>{{ $area->areaCode }}</strong></td>
                                            <td>
                                                {{ $area->areaName }}
                                                @if($isUsed)
                                                    <span class="badge bg-secondary bg-opacity-25 text-secondary ms-1">In use</span>
                                                @endif
                                            </td>
                                            <td>{{ $area->ajentName ?: '—' }}</td>
                                            <td class="text-end">
                                                @if($isUsed)
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete: used by customers">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-danger btn-delete-area"
                                                            title="Remove area"
                                                            data-id="{{ $area->id }}"
                                                            data-code="{{ $area->areaCode }}"
                                                            data-name="{{ $area->areaName }}">
                                                        <i class="bi bi-trash"></i>
                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="area-lookup-empty">
                                            <td colspan="4" class="text-muted text-center small py-3">No areas yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block mt-2">Area in use by a customer cannot be deleted.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm text-white" style="background:#0f2747;border-color:#0f2747;">
                        <i class="bi bi-save me-1"></i>Save Area
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .nc-card {
        box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
        border-radius: 4px;
        overflow: visible;
        border: 1px solid #d5dde8;
    }
    .nc-card-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        border: none;
        border-radius: 4px 4px 0 0;
        padding: 0.75rem 1rem;
    }
    .nc-label { font-size: 0.78rem; color: #0f2747; margin-bottom: 0.25rem; }
    .nc-form .form-control,
    .nc-form .form-select { font-size: 0.9rem; border-color: #d5dde8; }
    .nc-form .form-control:focus,
    .nc-form .form-select:focus {
        border-color: #1a4a8a;
        box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
    }
    .nc-form .btn-outline-secondary:hover {
        background: #0f2747;
        border-color: #0f2747;
        color: #fff;
    }
    .nc-tips {
        background: #f0f5fb;
        border: 1px solid #b8c9de;
        border-left: 4px solid #0f2747;
        border-radius: 4px;
        padding: 0.85rem 1rem;
    }
    .nc-modal-head {
        background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
        color: #fff;
    }
    .nc-area-table-wrap {
        max-height: 240px;
        overflow-y: auto;
        border: 1px solid #d5dde8;
        border-radius: 4px;
    }
    #areaLookupTable thead th {
        background: #0f2747;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        position: sticky;
        top: 0;
        z-index: 1;
        border: none;
        padding: 0.55rem 0.65rem;
    }
    #areaLookupTable tbody td {
        font-size: 0.85rem;
        padding: 0.45rem 0.65rem;
        border-color: #e8eef5;
    }
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #d5dde8 !important;
        min-height: 38px;
    }
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #1a4a8a !important;
        box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12) !important;
    }
    .select2-container--open { z-index: 1060 !important; }
    .select2-dropdown { z-index: 1061 !important; }
</style>
@endpush

<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $(document.body)
    });

    $('input[name="custCode"]').on('input', function() {
        if (this.value.trim() !== '') {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        }
    });

    $('input[name="custName"], input[name="bussnessName"], input[name="custCity"]').on('blur', function() {
        const words = this.value.toLowerCase().split(' ');
        this.value = words.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    });

    $('input[name="custContect"]').on('blur', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length === 11 && value.startsWith('03')) {
            this.value = value.substring(0, 4) + '-' + value.substring(4);
        }
    });

    // Area modal: Code UPPERCASE, Name/Agent Proper Case
    $('#areaModal input[name="areaCode"]').on('input', function() {
        if (this.value.trim() !== '') {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        }
    });
    $('#areaModal input[name="areaName"], #areaModal input[name="ajentName"]').on('blur', function() {
        if (!this.value.trim()) return;
        const words = this.value.toLowerCase().split(' ');
        this.value = words.map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    });

    setTimeout(() => $('.alert').fadeOut('slow'), 5000);
});

function openAreaModal() {
    $('#areaModal').modal('show');
}

function appendAreaTableRow(area) {
    $('#areaLookupBody .area-lookup-empty').remove();
    const agent = area.ajentName ? area.ajentName : '—';
    const row = `
        <tr class="area-lookup-item" data-id="${area.id}" data-code="${area.areaCode}" data-in-use="0">
            <td><strong>${area.areaCode}</strong></td>
            <td>${area.areaName}</td>
            <td>${agent}</td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-area"
                        title="Remove area" data-id="${area.id}" data-code="${area.areaCode}" data-name="${area.areaName}">
                <i class="bi bi-trash"></i>
            </button>
            </td>
        </tr>`;
    $('#areaLookupBody').prepend(row);
}

$('#areaForm').on('submit', function(e) {
    e.preventDefault();
    const $f = $(this);
    $('.error-areaCode, .error-areaName, .error-ajentName').text('');

    $.ajax({
        url: '{{ route("area.store") }}',
        method: 'POST',
        data: $f.serialize(),
        dataType: 'json',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            if (response.success) {
                const area = response.area;
                const exists = $('#customerAreaSelect option').filter(function() {
                    return String($(this).val()).toUpperCase() === String(area.areaCode).toUpperCase();
                }).length > 0;

                if (!exists) {
                    $('#customerAreaSelect').append(
                        $('<option>', {
                            value: area.areaCode,
                            text: area.areaCode + ' - ' + area.areaName,
                            selected: true
                        })
                    ).trigger('change');
                    appendAreaTableRow(area);
                } else {
                    $('#customerAreaSelect').val(area.areaCode).trigger('change');
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Area added successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }

                $f[0].reset();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                const errors = xhr.responseJSON.errors;
                if (errors.areaCode) $('.error-areaCode').text(errors.areaCode[0]);
                if (errors.areaName) $('.error-areaName').text(errors.areaName[0]);
                if (errors.ajentName) $('.error-ajentName').text(errors.ajentName[0]);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to add area.'
                });
            }
        }
    });
});

$(document).on('click', '.btn-delete-area', function() {
    const id = $(this).data('id');
    const code = $(this).data('code');
    const name = $(this).data('name');
    const $btn = $(this);

    const doDelete = function() {
        $.ajax({
            url: '{{ url('areas') }}/' + id,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE'
            },
            dataType: 'json',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                if (response.success) {
                    $btn.closest('.area-lookup-item').remove();
                    $('#customerAreaSelect option').filter(function() {
                        return String($(this).val()).toUpperCase() === String(code).toUpperCase();
                    }).remove();

                    if (String($('#customerAreaSelect').val() || '').toUpperCase() === String(code).toUpperCase()) {
                        $('#customerAreaSelect').val('').trigger('change');
                    }

                    if ($('#areaLookupBody .area-lookup-item').length === 0) {
                        $('#areaLookupBody').html('<tr class="area-lookup-empty"><td colspan="4" class="text-muted text-center small py-3">No areas yet.</td></tr>');
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: response.message || 'Area removed.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            },
            error: function(xhr) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Cannot Delete',
                        text: xhr.responseJSON?.message || 'Failed to delete area.'
                    });
                } else {
                    alert(xhr.responseJSON?.message || 'Failed to delete area.');
                }
            }
        });
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Remove area?',
            text: 'Delete "' + name + '" (' + code + ') from the list?',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then(function(result) {
            if (result.isConfirmed) doDelete();
        });
    } else if (confirm('Delete "' + name + '"?')) {
        doDelete();
    }
});

$('#areaModal').on('hidden.bs.modal', function() {
    $('#areaForm')[0].reset();
    $('.error-areaCode, .error-areaName, .error-ajentName').text('');
});

function clearCustomerFieldErrors() {
    $('#customerForm .is-invalid').removeClass('is-invalid');
    $('#customerForm .nc-ajax-error').remove();
    $('#customerForm .alert-danger').remove();
}

function showCustomerFieldErrors(errors) {
    Object.keys(errors || {}).forEach(function(field) {
        const $input = $('#customerForm [name="' + field + '"]');
        if (!$input.length) return;
        $input.addClass('is-invalid');
        const msg = $('<div class="invalid-feedback d-block nc-ajax-error"></div>').text(errors[field][0]);
        if ($input.hasClass('select2-hidden-accessible')) {
            $input.next('.select2').after(msg);
        } else {
            $input.after(msg);
        }
    });
}

function focusCustomerCode() {
    const $code = $('#customerForm input[name="custCode"]');
    if ($code.length) {
        $code.trigger('focus');
        if (typeof $code[0].select === 'function') $code[0].select();
    }
}

function resetCustomerFormKeepDefaults() {
    const form = document.getElementById('customerForm');
    if (!form) return;
    const keptArea = $('#customerAreaSelect').val();
    form.reset();
    // Keep Area selected after save
    $('#customerAreaSelect').val(keptArea || '').trigger('change');
    const $date = $('#customerForm [name="custOppDate"]');
    if ($date.length) {
        $date.val(@json(now()->format('Y-m-d')));
    }
    clearCustomerFieldErrors();
}

$('#customerForm').on('submit', function(e) {
    e.preventDefault();
    clearCustomerFieldErrors();
    
    const $btn = $(this).find('button[type="submit"]').prop('disabled', true);
    const originalHtml = $btn.html();
    $btn.html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    
    $.ajax({
        url: this.action,
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    }).done(function(res) {
        resetCustomerFormKeepDefaults();

        const message = (res && res.message) ? res.message : 'Customer created successfully!';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0f2747',
                allowOutsideClick: false,
                allowEscapeKey: true,
                didOpen: function() {
                    const btn = Swal.getConfirmButton();
                    if (btn) btn.focus();
                }
            }).then(function() {
                focusCustomerCode();
            });
        } else {
            alert(message);
            focusCustomerCode();
        }
    }).fail(function(xhr) {
        const errors = xhr.responseJSON && xhr.responseJSON.errors;
        if (errors) {
            showCustomerFieldErrors(errors);
            const first = Object.values(errors)[0];
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation',
                    text: (first && first[0]) || 'Please fix the errors.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#0f2747',
                    didOpen: function() {
                        const btn = Swal.getConfirmButton();
                        if (btn) btn.focus();
                    }
                });
            }
            return;
        }
        const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not save customer.';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: msg,
                confirmButtonText: 'OK',
                confirmButtonColor: '#0f2747',
                didOpen: function() {
                    const btn = Swal.getConfirmButton();
                    if (btn) btn.focus();
                }
            });
        } else {
            alert(msg);
        }
    }).always(function() {
        $btn.prop('disabled', false).html(originalHtml);
    });
});
</script>

@endsection

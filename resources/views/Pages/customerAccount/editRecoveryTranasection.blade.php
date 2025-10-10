@extends('layout.app')

@section('title', 'Dashboard')

@section('content')

<main class="app-main" id="main" tabindex="-1">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Edit Recovery Transection</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Edit Recovery</li>
                </ol>
              </div>
              <hr style="border-top: 1px solid #140097; md-3">
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
            <form id="recoveryForm" method="POST" action="{{ route('ledgerTransection.update', $voucher->id) }}">
              @csrf
              @method('PUT')

              <div class="col-md-12">
                <div class="container-fluid">
                    <div class="row g-2 mb-2">
                      <div class="col-md-3">
                        <label class="form-label mb-1">Select Customer Area</label>
                        <select class="form-select select2 form-select-sm" name="customerArea" id="slectArea">
                          <option value="" disabled {{ old('customerArea', $voucher->customerArea) ? '' : 'selected' }}>-- Select Area --</option>
                          @foreach($areas as $area)
                            <option value="{{ $area->areaCode }}"
                              {{ old('customerArea', $voucher->customerArea) == $area->areaCode ? 'selected' : '' }}>
                              {{ $area->areaCode }} ({{ $area->areaName }})
                            </option>
                          @endforeach
                        </select>
                        <span class="error text-danger" id="areaError"></span>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label mb-1">Pre Balance</label>
                        <input class="form-control form-control-sm" type="number" value="{{ $voucher->preBalance }}" name="preBalance" id="preBalance" readonly>
                      </div>
                      <div class="col-md-1">
                        <label class="form-label mb-1">After Rec</label>
                        <input class="form-control form-control-sm" type="number" id="balance1" name="Balance1" readonly>
                      </div>
                      <div class="col-md-1">
                        <label class="form-label mb-1">After Disc</label>
                        <input class="form-control form-control-sm" type="number" id="balance2" name="Balance2" readonly>
                      </div>
                      <div class="col-md-1">
                        <label class="form-label mb-1">Last Balance</label>
                        <input class="form-control form-control-sm" type="number" id="lastBalance" name="lastBalance" readonly>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label mb-1">Recovery B</label>
                        <input class="form-control form-control-sm text-primary" type="number" id='recoveryBalance'  name="recoveryBalance" placeholder="" >
                      </div>
                      <div class="col-md-2">
                        <label class="form-label mb-1">Date</label>
                        <input class="form-control form-control-sm text-primary" type="date" name="customerDate"  value="{{ $voucher->voucher_date->format('Y-m-d') }}" >
                      </div>
                    </div>
                     <div class="card">
                      <div class="card-body table-wrapper">
                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label mb-1">Select Customer Code</label>
                            <select class="form-select select2 form-select-sm" name="customerCode" id="customerSelect">
                              <option value="" disabled>-- Select Customer --</option>
                              
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label mb-1">Discription</label>
                            <input type="text" class="form-control form-control-sm" name="description"  id="description" autocomplete="off" value="{{ $voucher->description }}">
                            <span class="error text-danger" id="descriptionError"></span>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label mb-1">Recovery</label>
                            <input type="number" class="form-control form-control-sm" id="marketRecovery" name="marketRecovery" value="{{ $voucher->recovery }}">
                            <span class="error text-danger" id="recoveryError"></span>  
                          </div>
                          <div class="col-md-1">
                            <label class="form-label mb-1">Discount</label>
                            <input type="number" class="form-control form-control-sm" id="voucherDiscount" name="voucherDiscount" value="{{ $voucher->discount }}">
                            <span class="error text-danger" id="discountError"></span>
                          </div>
                          <div class="col-md-1">
                            <label class="form-label mb-1">Bilty or Misc</label>
                            <input type="number" class="form-control form-control-sm" id="biltyMisc" name="misc" value="{{ $voucher->misc }}">
                            <span class="error text-danger" id="biltyMiscError"></span>
                          </div>
                          <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                              Save
                            </button>
                          </div>
                        </div>
                      </form>
                  </div>
              </div>
            </div>
            <!-- /.card -->
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
        <!--end::Row-->
      </div>
      <!--end::Container-->
    </div>
    <!--end::App Content-->
  </main>
      <script>
        

// --- Balance Calculation ---
function calculateBalance() {
    let preBalance = parseFloat($('#preBalance').val()) || 0;
    let recovery   = parseFloat($('#marketRecovery').val()) || 0;
    let discount   = parseFloat($('#voucherDiscount').val()) || 0;
    let misc       = parseFloat($('#biltyMisc').val()) || 0;

    let balance1 = preBalance - recovery;
    let balance2 = balance1 - discount;
    let lastBalance = balance2 - misc;

    $('#balance1').val(balance1);
    $('#balance2').val(balance2);
    $('#lastBalance').val(lastBalance);
}
$('#marketRecovery, #voucherDiscount, #biltyMisc').on('input', calculateBalance);



$(document).ready(function() {
    let selectedArea = "{{ old('customerArea', $voucher->customerArea) }}";
    let selectedCustomer = "{{ old('customerCode', $voucher->customerCode) }}";

    function loadCustomers(areaCode, selectedCustomer = null) {
        if (!areaCode) return;

        $.get("/ledgerTransection/get-customers/" + areaCode, function(data) {
            let $customerSelect = $('#customerSelect');
            $customerSelect.empty().append('<option value="">-- Select Customer --</option>');

            // ✅ customers loop
            $.each(data.customers, function(index, customer) {
                $customerSelect.append(
                    $('<option>', {
                        value: customer.custCode,
                        text: customer.custCode + ' (' + customer.custName + ')',
                        selected: (customer.custCode == selectedCustomer)
                    })
                );
            });

            $customerSelect.trigger('change'); // refresh select2
        });
    }

    // Area change → customers reload
    $('#slectArea').on('change', function() {
        loadCustomers($(this).val());
    });

    $('#customerSelect').on('change', function() {
        let custCode = $(this).val();
        if (!custCode) return;

        $.get("/ledgerTransection/balance/" + custCode, function(data) {
           // $('#preBalance').val(data.balance); // 👈 input field update
        });
    });

    // Page load → agar edit form already area select
    if (selectedArea) {
        loadCustomers(selectedArea, selectedCustomer);
    }
});

        
        
        </script>


        

      @endsection
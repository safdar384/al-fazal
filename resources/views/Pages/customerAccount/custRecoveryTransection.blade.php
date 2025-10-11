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
          <div class="col-sm-6"><h3 class="mb-0">Customer Recovery Transection</h3></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item active" aria-current="page">Customer Recovery</li>
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
            <form id='recoveryForm' method="POST" action="{{ route('ledgerTransection.store') }}">
                @csrf
              <div class="col-md-12">
                <div class="container-fluid">
                    <div class="row g-2 mb-2">
                      <div class="col-md-3">
                        <label class="form-label mb-1">Select Customer Area</label>
                        <select class="form-select select2 form-select-sm" name="customerArea" id="slectArea">
                          <option value="" selected disabled>-- Select Area --</option>
                          @foreach($areas as $area)
                            <option value="{{ $area->areaCode }}">{{ $area->areaCode }} ({{ $area->areaName }})</option>
                          @endforeach
                        </select>
                        <span class="error text-danger" id="areaError"></span>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label mb-1">Pre Balance</label>
                        <input class="form-control form-control-sm" type="number" name="preBalance" id="preBalance" readonly>
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
                        <a href="#" onclick="openModal()">Transfer</a>
                        <input class="form-control form-control-sm text-primary" type="number" id='recoveryBalance'  name="recoveryBalance" placeholder="" >
                      </div>
                      <div class="col-md-2">
                        <label class="form-label mb-1">Date</label>
                        <input class="form-control form-control-sm text-primary" type="date" name="customerDate" value="{{ now()->format('Y-m-d') }}" >
                      </div>
                    </div>
                     <div class="card">
                      <div class="card-body table-wrapper">
                        <div class="row g-2 mb-2">
                          <div class="col-md-4">
                            <label class="form-label mb-1">Select Customer Code</label>
                            <select class="form-select select2 form-select-sm" name="customerCode" id="customerCode" value="{{ old('custCode') }}">
                            <option value="" selected disabled>-- Select customer --</option>
                            </select>
                            <span class="error text-danger" id="customerCodeError"></span>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label mb-1">Discription</label>
                            <input type="text" class="form-control form-control-sm" name="description"  id="description" autocomplete="off" value="{{ old('description') }}">
                            <span class="error text-danger" id="descriptionError"></span>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label mb-1">Recovery</label>
                            <input type="number" class="form-control form-control-sm" id="marketRecovery" name="marketRecovery" value="{{ old('marketRecovery') }}">
                            <span class="error text-danger" id="recoveryError"></span>  
                          </div>
                          <div class="col-md-1">
                            <label class="form-label mb-1">Discount</label>
                            <input type="number" class="form-control form-control-sm" id="voucherDiscount" name="voucherDiscount" value="{{ old('voucherDiscount') }}">
                            <span class="error text-danger" id="discountError"></span>
                          </div>
                          <div class="col-md-1">
                            <label class="form-label mb-1">Bilty or Misc</label>
                            <input type="number" class="form-control form-control-sm" id="biltyMisc" name="biltyMisc" value="{{ old('biltyMisc') }}">
                            <span class="error text-danger" id="biltyMiscError"></span>
                          </div>
                          <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                              Save
                            </button>
                          </div>
                        </div>
                      </form>
                        <table class="table table-bordered table-hover" id="agentTable">
                          <thead class="table-primary">
                              <tr>
                                  <th>ID</th>
                                  <th>Area</th>
                                  <th>Customer</th>
                                  <th>City</th>
                                  <th>Pre Balance</th>
                                  <th>Recovery</th>
                                  <th>Discount</th>
                                  <th>Misc</th>
                                  <th>Last Balance</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>
                              <!-- new rows append honge yahan -->
                          </tbody>
                      </table>

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

      <!-- model area banalnce transfer to main aacount -->
        <div class="modal fade" id="entryModal" tabindex="-1" aria-labelledby="entryModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="entryModalLabel">Add Credit Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">
                <div class="row g-3">
                  <!-- Credit Account -->
                  <div class="col-md-6">
                    <label class="form-label">Select Credit Account</label>
                    <select class="form-select select2" id="creditAccount" style="width: 100%;">
                      <option value="">-- Select Account --</option>
                      <option value="101">101 - Cash</option>
                      <option value="102">102 - Bank</option>
                      <option value="201">201 - Supplier</option>
                      <!-- yahan dynamic accounts bhi load kar sakte ho -->
                    </select>
                  </div>

                  <!-- Amount -->
                  <div class="col-md-6">
                    <label class="form-label">Amount</label>
                    <input type="number" class="form-control" id="amount" step="0.01">
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="saveEntry">Save</button>
              </div>
            </div>
          </div>
        </div>


  <script>

      // Open Area Modal
      function openModal() {
          const modal = new bootstrap.Modal(document.getElementById('entryModal'), {
              backdrop: 'static',
              keyboard: false
          });
          modal.show();
      }


        // --- Area Change: Load customers + balance ---
      $('#slectArea').on('change', function () {
          let areaCode = $(this).val();

          fetch(`/ledgerTransection/get-customers/${areaCode}`)
              .then(res => res.json())
              .then(data => {
                  // Reset customerCode dropdown
                  let $select = $('#customerCode');
                  $select.empty().append('<option disabled selected>Choose Code</option>');

                  // Add customers
                  data.customers.forEach(c => {
                      $select.append(`<option value="${c.custCode}">${c.custCode} - ${c.custName}</option>`);
                  });

                  // Refresh select2
                  $select.val(null).trigger('change');

                  // Balance field
                  $('#recoveryBalance').val(data.balance ?? 0);
              })
              .catch(err => console.error(err));
      });

      // --- Customer Change: Load balance ---
      $('#customerCode').on('change', function () {
          let customerCode = $(this).val();

          $.ajax({
              url: '/ledgerTransection/balance/' + customerCode,
              type: 'GET',
              success: function (res) {
                  $('#preBalance').val(res.balance ?? 0);
              },
              error: function (xhr) {
                  console.error(xhr.responseText);
                  alert("Error loading balance!");
              }
          });
      });

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

      // --- Load Ledger Records ---
      function loadLedger(areaCode) {
          $.ajax({
              url: '/ledgerTransection/by-area/' + areaCode,
              method: 'GET',
              success: function (res) {
                  let rows = '';
                  if (res.length > 0) {
                      res.forEach(v => {
                          rows += `
                              <tr>
                                  <td>${v.id}</td>
                                  <td>${v.customerArea}</td>  
                                  <td>${v.custAddress}</td>
                                  <td>${v.custCity}</td>
                                  <td>${v.preBalance}</td>
                                  <td>${v.recovery}</td>
                                  <td>${v.discount}</td>
                                  <td>${v.misc}</td>
                                  <td>${v.lastBalance}</td>
                                  <td>
                                      <a href="/ledgerTransection/${v.id}/edit" onclick="openEditModal()" class="btn btn-sm btn-primary">Edit</a>
                                      <button class="btn btn-sm btn-danger delete-btn" data-id="${v.id}">Delete</button>
                                  </td>
                              </tr>
                          `;
                      });
                  } else {
                      rows = `<tr><td colspan="6" class="text-center">No record found for today</td></tr>`;
                  }
                  $('#agentTable tbody').html(rows);
              }
          });
      }

     //submit form ajax save data 
      $('#recoveryForm').on('submit', function (e) {
            e.preventDefault();

            // validation...
            let fields = ['slectArea', 'customerCode', 'description', 'marketRecovery', 'voucherDiscount', 'biltyMisc'];
            let valid = true;

            fields.forEach(f => {
                let el = document.getElementById(f);
                el.classList.remove('is-invalid');

                if (!el.value || el.value < 0 || (f === 'marketRecovery' && el.value <= 0)) {
                    el.classList.add('is-invalid');
                    valid = false;
                }
            });

            if (!valid) return;

            $.ajax({
                url: '/ledgerTransection/save',
                method: 'POST',
                data: $(this).serialize(),
                success: function () {
                    // balances reset
                    $('#preBalance, #balance1, #balance2, #lastBalance, #marketRecovery, #voucherDiscount, #biltyMisc').val('');
                    $('#customerCode').val(null).trigger('change');

                    // reload table from DB (so no duplicate logic)
                    let areaCode = $('#slectArea').val();
                    if (areaCode) loadLedger(areaCode);
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    alert("Error saving recovery!");
                }
            });
        });
         
     // --- Initialize Select2 ---
        $('#slectArea').select2({
            placeholder: "Select Area...",
            allowClear: true,
            width: '100%'
        });

        // --- Select area change ---
        $('#slectArea').on('select2:select', function () {
            let areaCode = $(this).val();
            if (areaCode) {
                loadLedger(areaCode);
            }
        });

        // --- If cleared ---
        $('#slectArea').on('select2:clear', function () {
            $('#agentTable tbody').html('<tr><td colspan="6" class="text-center">No record found</td></tr>');
        });


      $('#customerCode').select2({
          placeholder: "Select Customer...",
          allowClear: true,
          width: '100%'
      });

  </script>

@endsection
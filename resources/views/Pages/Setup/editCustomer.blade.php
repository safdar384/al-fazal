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
          <div class="col-sm-6"><h3 class="mb-0">Edit Customer</h3></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/Customer') }}">Customer</a></li>
              <li class="breadcrumb-item active" aria-current="page">Edit Customer</li>
            </ol>
          </div>
        </div>
        <!--end::Row-->
      </div>
      <!--end::Container-->
    </div>
    <div class="container mt-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Edit Customer Entry</h5>
      </div>

      <form action="{{ route('Customer.update', $customer->id) }}" method="POST">
         @csrf
        @method('PUT') 
        <div class="card-body">
          <div class="row g-6">
            <div class="col-md-3">
              <label class="form-label">Bussnes Code</label>
              <input type="text" class="form-control" name="custCode" value="{{ $customer->custCode }}" autocomplete="off">
            </div>
            <div class="col-md-3">
                <label class="form-label">Customer Area</label>
                <select class="form-select select2 w-100" name="custArea" id="customerAreaSelect" autocomplete="off">
                    <option value=""></option>
                    @foreach($custAreas as $custArea)
                        <option value="{{ $custArea->areaCode }}"
                            {{ old('custArea', $customer->custArea ?? '') == $custArea->areaCode ? 'selected' : '' }}>
                            {{ $custArea->areaCode }} ({{ $custArea->areaName }})
                        </option>
                    @endforeach
                </select>
            </div>
          </div>
        </div> 
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Bussness Name</label>
              <input type="text" class="form-control" name="bussnessName" value="{{ $customer->bussnessName }}" autocomplete="off">
            </div>
            <div class="col-md-3">
              <label class="form-label">Customer Name</label>
              <input type="text" class="form-control" name="custName" value="{{ $customer->custName }}" autocomplete="off">
            </div>

            <div class="col-md-9">
              <label class="form-label">Customer Address</label>
              <input type="text" class="form-control " name="custAddress" value="{{ $customer->custAddress }}"  autocomplete="off">
            </div>

            <div class="col-md-3">
              <label class="form-label">Customer Contact</label>
              <input type="text" class="form-control" name="custContect" value="{{ $customer->custContect }}" autocomplete="off">
            </div>

            <div class="col-md-3">
              <label class="form-label">Customer City</label>
              <input type="text" class="form-control" name="custCity" value="{{ $customer->custCity }}" autocomplete="off">
            </div>

            <div class="col-md-3">
              <label class="form-label">Customer Page</label>
              <input type="text" class="form-control" name="custPage" value="{{ $customer->custPage }}" autocomplete="off">
            </div>  

            <div class="col-md-3">
              <label class="form-label">Customer Discount (%)</label>
              <input type="number" step="0.01" class="form-control" name="custdiscount" value="{{ $customer->custdiscount }}" autocomplete="off">
            </div>
            <div class="col-md-3">
              <label class="form-label">Payment Term days</label>
              <input type="text" class="form-control" name="custPayTermDay" value="{{ $customer->custPayTermDay }}" autocomplete="off">
            </div>

            <div class="col-md-3">
              <label class="form-label">Caradit Limit</label>
              <input type="text" step="0.01" class="form-control" name="custCreditLimit" value="{{ $customer->custCreditLimit }}" autocomplete="off">
            </div>

            <div class="col-md-3">
              <label class="form-label">Oppning Date</label>
               <input type="date" class="form-control" name="custOppDate" value="{{ $customer->custOppDate }}"  autocomplete="off">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Customer Category</label>
                <select class="form-select select2 w-100" name="custCatagory" id="custCatagorySelect" autocomplete="off">
                    <option value="">-- Select Category --</option>
                    <option value="Retail" {{ old('custCatagory', $customer->custCatagory ?? '') == 'Retail' ? 'selected' : '' }}>Retail</option>
                    <option value="Wholesale" {{ old('custCatagory', $customer->custCatagory ?? '') == 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                    <option value="VIP" {{ old('custCatagory', $customer->custCatagory ?? '') == 'VIP' ? 'selected' : '' }}>VIP</option>
                </select>
            </div>

            </div>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary" >Cancel</button>
          <button type="submit" class="btn btn-primary">Save Customer</button>
        </div>
      </form>
    </div>
  </div>
</main>

      <!-- Modal -->
      <!-- <div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true"> -->
        <!-- Area Modal (won’t close the other modal) -->
      <div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form method="POST" action="">
              @csrfa
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="areaModalLabel">Add New Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label>Area Code</label>
                  <input type="text" name="areaCode" class="form-control" autocomplete="off">
                </div>

                <div class="mb-3">
                  <label>Area Name</label>
                  <input type="text" name="areaName" class="form-control" autocomplete="off">
                </div>

                <div class="mb-3">
                  <label>Ajent Name</label>
                  <input type="text" name="ajentName" class="form-control" autocomplete="off">
                </div>
              </div>

              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <script>
       document.addEventListener("DOMContentLoaded", function () {
                const areaInput = document.getElementById("areaSearch");          // ✅ matches input
                const nameInput = document.getElementById("searchCustomer");      // ✅ matches input
                const table = document.getElementById("customerTable").getElementsByTagName("tbody")[0];

                function filterTable() {
                    const areaVal = areaInput.value.toLowerCase();
                    const nameVal = nameInput.value.toLowerCase();

                    Array.from(table.rows).forEach(row => {
                        const areaText = row.cells[1].textContent.toLowerCase();
                        const nameText = row.cells[2].textContent.toLowerCase();

                        const match = areaText.includes(areaVal) &&
                                      nameText.includes(nameVal);

                        row.style.display = match ? "" : "none";
                    });
                }

                areaInput.addEventListener("input", filterTable);
                nameInput.addEventListener("input", filterTable);
            });


          function openAreaModal() {
            const areaModal = new bootstrap.Modal(document.getElementById('areaModal'), {
              backdrop: 'static',
              keyboard: false
            });
            areaModal.show();
          }

  $(document).ready(function () {
    // Re-initialize Select2 every time modal is shown
    $('#customerModal').on('shown.bs.modal', function () {
      $('#customerCategorySelect').select2({
        dropdownParent: $('#customerModal'),
        placeholder: "Choose category",
        allowClear: true
      });
    });
  });

  
$(document).ready(function() {
      // Single select
      $('#customerAreaSelect').select2({
        placeholder: "Search a colour...",
        allowClear: true,
        width: '100%'
      });

      $('#custCatagorySelect').select2({
        placeholder: "Search a colour...",
        allowClear: true,
        width: '100%',
        color: '#001ba3'
      });
    });
          
        
        </script>

      @endsection
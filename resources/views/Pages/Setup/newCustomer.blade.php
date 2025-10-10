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
          <div class="col-sm-6"><h3 class="mb-0">New Customer</h3></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/Customer') }}">Customer</a></li>
              <li class="breadcrumb-item active" aria-current="page">Add New Customer</li>
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
        <h5 class="mb-0">New Customer Entry</h5>
      </div>

     <form action="{{ route('Customer.store') }}" method="POST" id="saveForm">
       @csrf
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Customer Area</label>
              <a href="#" onclick="openAreaModal()">Add Area</a>
              <select class="form-select select2 w-100" name="custArea" id="customerAreaSelect" autocomplete="off">
                  <option value=""></option>
                  @foreach($custAreas as $custArea)
                      <option value="{{ $custArea->areaCode }}"
                          {{ old('custArea', $customer->areaCode ?? '') == $custArea->areaCode ? 'selected' : '' }}>
                          {{ $custArea->areaCode }} ({{ $custArea->areaName }})
                      </option>
                  @endforeach
              </select>
              @error('custArea')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
          </div>
        </div> 
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Bussnes Code</label>
              <input type="text" class="form-control" name="custCode" value="{{ old('custCode') }}" autocomplete="off">
                @error('custCode')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Bussness Name</label>
              <input type="text" class="form-control" name="bussnessName" value="{{ old('bussnessName') }}" autocomplete="off">
                @error('bussnessName')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Customer Name</label>
              <input type="text" class="form-control" name="custName" value="{{ old('custName') }}" autocomplete="off">
                @error('custName')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-12">
              <label class="form-label">Customer Address</label>
              <input type="text" class="form-control " name="custAddress" value="{{ old('custAddress') }}" autocomplete="off">
                @error('custAddress')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Customer Contact</label>
              <input type="text" class="form-control" name="custContect" value="{{ old('custContect') }}"   autocomplete="off">
                @error('custContect')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Customer City</label>
              <input type="text" class="form-control" name="custCity" value="{{ old('custCity') }}"  autocomplete="off">
                @error('custCity')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Customer Page</label>
              <input type="text" class="form-control" name="custPage" value="{{ old('custPage') }}" autocomplete="off">
                @error('custPage')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Customer Discount (%)</label>
              <input type="number" step="0.01" class="form-control" name="custdiscount" value="{{ old('custdiscount') }}" autocomplete="off">
                @error('custdiscount')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label">Payment Term days</label>
              <input type="text" class="form-control" name="custPayTermDay" value="{{ old('custPayTermDay') }}" autocomplete="off">
                @error('custPayTermDay')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Caradit Limit</label>
              <input type="text" step="0.01" class="form-control" name="custCreditLimit" value="{{ old('custCreditLimit') }}" autocomplete="off">
                @error('custCreditLimit')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Oppning Date</label>
               <input type="date" class="form-control" name="custOppDate" value="{{ old('custOppDate', now()->format('Y-m-d')) }}"  autocomplete="off">
                @error('custOppDate')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Customer Category</label>
              <select class="form-select select2" name="custCatagory" id="customerCategorySelect" value="{{ old('custCatagory') }}"   autocomplete="off">
                <option value="">-- Select Catagory --</option>
                <option value="Retail">Customer</option>
                <option value="Wholesale">Wholesale</option>
                <option value="VIP">Supplier</option>
              </select>
              @error('custCatagory')
                  <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ url('/Customer') }}'">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="showConfirm('saveForm')">Save Customer</button>
        </div>
      </form>
    </div>
  </div>
</main>



      <!-- Modal -->
      <!-- <div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true"> -->
        <!-- Area Modal (won’t close the other modal) -->
    <div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
              <form action="{{ route('area.store') }}" method="POST" id="areaForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add New Area</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Area Code</label>
                        <input type="text" name="areaCode" class="form-control form-control-sm" value="{{ old('areaCode') }}" autocomplete="off">
                        <span class="text-danger error-areaCode"></span>
                      </div>
                    <div class="mb-2">
                        <label>Area Name</label>
                        <input type="text" name="areaName" class="form-control form-control-sm" value="{{ old('areaName') }}" autocomplete="off">
                        <span class="text-danger error-areaName"></span>  
                      </div>
                    <div class="mb-2">
                        <label>Ajent Name</label>
                        <input type="text" name="ajentName" class="form-control form-control-sm" value="{{ old('ajentName') }}" autocomplete="off">
                        <span class="text-danger error-ajentName"></span>
                      </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">Save</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                </div>
              </form>
                <div class="card m-2">
                    <div class="card-body p-2">
                        <div style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-bordered table-sm mb-0" id="areaTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>No.</th>
                                        <th>Area Code</th>
                                        <th>Area Name</th>
                                        <th>Ajent Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($custAreas as $custArea)
                                      <tr>
                                          <td>{{ $custArea->id }}</td>
                                          <td>{{ $custArea->areaCode }}</td>
                                          <td>{{ $custArea->areaName }}</td>
                                          <td>{{ $custArea->ajentName }}</td>
                                          <td>
                                          <form action="{{ route('areas.destroy', $custArea->id ) }}" method="POST" style="display:inline;">
                                              @csrf
                                              @method('DELETE')
                                              <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                  Delete
                                              </button>
                                          </form>
                                          </td>
                                      </tr>
                                      @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>


      <script>
// Open Area Modal
function openAreaModal() {
    const modal = new bootstrap.Modal(document.getElementById('areaModal'), {
        backdrop: 'static',
        keyboard: false
    });
    modal.show();
}


$('#areaForm').submit(function(e){
    e.preventDefault();

    // clear old errors
    $('.error-areaCode').text('');
    $('.error-areaName').text('');
    $('.error-ajentName').text('');

    $.ajax({
        url: "{{ route('area.store') }}",
        type: "POST",
        data: $(this).serialize(),
        success: function(res){
            if(res.success){
                // Format: areaName (areaCode)
                let text = res.data.areaName + " (" + res.data.areaCode + ")";
                let opt  = new Option(text, res.data.areaCode, true, true);

                $('#customerAreaSelect').append(opt).trigger('change');

                // Close modal & reset form
                $('#areaModal').modal('hide');
                $('#areaForm')[0].reset();
            }
        },
        error: function(xhr){
            if(xhr.status === 422){
                let errors = xhr.responseJSON.errors;
                if(errors.areaCode){
                    $('.error-areaCode').text(errors.areaCode[0]);
                }
                if(errors.areaName){
                    $('.error-areaName').text(errors.areaName[0]);
                }
                if(errors.ajentName){
                    $('.error-ajentName').text(errors.ajentName[0]);
                }
            }
        }
    });
});

$(document).ready(function() {
    // Area Select2
    $('#customerAreaSelect').select2({
        placeholder: "Search an area...",
        allowClear: true,
        width: '100%',
        templateSelection: function (data) {
            if (!data.id) return data.text; // for placeholder
            return data.id; // show only areaCode after select
        },
        templateResult: function (data) {
            return data.text; // dropdown still shows full text: MGP (MagrabiPunjab)
        }
    });

    // Category Select2
    $('#customerCategorySelect').select2({
        placeholder: "Search a category...",
        allowClear: true,
        width: '100%'
    });
});


</script>

      @endsection
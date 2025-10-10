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
              <div class="col-sm-6"><h3 class="mb-0">New Product Information</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item"><a href="{{ url('/product') }}">Product</a></li>
                  <li class="breadcrumb-item active" aria-current="page">New Product</li>
                </ol>
              </div>
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
              <div class="col-md-12">
                <div class="card">
                  <form method="POST" action="{{route('product.store')}}" id="saveForm">
                    @csrf
                    <div class="card-body">
                      <div class="row g-3">
                        <div class="col-md-3">
                          <label class="form-label">Product Code</label>
                          <input type="text" class="form-control" name="productCode" value="{{ old('productCode') }}" autocomplete="off">
                          @error('productCode')
                            <span class="text-danger">{{ $message }}</span>
                          @enderror
                        </div>
                        <div class="col-md-9">
                          <label class="form-label">Product Name</label>
                          <input type="text" class="form-control" name="productName" value="{{ old('productName') }}" autocomplete="off">
                          @error('productName')
                            <span class="text-danger">{{ $message }}</span>
                          @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Product Company</label>
                            <a href="#" onclick="openModal('companyModal')">Add Company</a>
                            <select class="form-select select2 w-100" name="companyName" id="companySelect" autocomplete="off">
                              <option selected disabled>Choose Company</option>  
                              @foreach($proCompanys as $company)
                                    <option value="{{ $company->companyName }}" 
                                        {{ old('companyName') == $company->companyName ? 'selected' : '' }}>
                                        {{ $company->companyName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('companyName')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">Colour</label>
                          <a href="#" onclick="openModal('coloureModal')">Add Colour</a>
                          <select class="form-select select2 w-100" name="colourName"  id="colourSelect" style="">
                            <option selected disabled>Choose Colour</option>
                            @foreach($colours as $colour)
                              <option value="{{ $colour->colourName }}" 
                                  {{ old('colourName') == $colour->colourName ? 'selected' : '' }}>
                                  {{ $colour->colourName }}
                              </option>
                            @endforeach
                          </select>
                           @error('colourName')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                            <div class="col-md-4">
                            <label class="form-label">Packing</label>
                            <a href="#" onclick="openModal('pakingModal')">Add Packing</a>
                            <select class="form-select select2" name="pakingName" id="packingSelect">
                                <option selected disabled>Choose Packing</option>
                                @foreach($packings as $packing)
                                    <option value="{{ $packing->pakingName }}" 
                                        {{ old('pakingName') == $packing->pakingName ? 'selected' : '' }}>
                                        {{ $packing->pakingName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pakingName')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-4">
                          <label class="form-label">Sale Rate</label>
                          <input type="number" step="0.01" class="form-control" name="saleRate" value="{{ old('saleRate')}}" autocomplete="off">
                          @error('saleRate')
                            <span class="text-danger">{{ $message }}</span>
                          @enderror
                        </div>
                        
                        <div class="col-md-4">
                          <label class="form-label">Cost Rate</label>
                          <input type="number" step="0.01" class="form-control" name="costRate" value="{{ old('costRate') }}" autocomplete="off">
                          @error('costRate')
                            <span class="text-danger">{{ $message }}</span>
                          @enderror
                        </div>

                        <div class="col-md-4">
                          <label class="form-label">Less Quantity</label>
                          <input type="number" step="0.01" class="form-control" name="lessQty" value="{{ old('lessQty') }}" autocomplete="off">
                          @error('lessQty')
                            <span class="text-danger">{{ $message }}</span>
                          @enderror
                        </div>
                        
                        <div class="col-md-4">
                          <label class="form-label">Stock Quantity</label>
                          <input type="number" step="0.01" class="form-control" name="stockQty" value="{{ old('stockQty') }}" autocomplete="off">
                          @error('stockQty')
                            <span class="text-danger">{{ $message }}</span>
                          @enderror
                        </div>
                        
                        <div class="col-md-4">
                          <label class="form-label">Category</label>
                          <a href="#" onclick="openModal('catagoryModal')">Add Catagory</a>
                          <select class="form-select select2" name="catagoryName" id="CategorySelect">
                            <option selected disabled>Choose Catagory</option>
                            @foreach($catagorys as $catagory)
                                <option value="{{ $catagory->catagoryName }}" 
                                    {{ old('catagoryName') == $catagory->catagoryName ? 'selected' : '' }}>
                                    {{ $catagory->catagoryName }}
                                </option>
                            @endforeach
                          </select>
                          @error('catagoryName')
                              <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                      </div>
                    </div>
                    <div class="card-footer text-end">
                      <a href="{{ route('product.index') }}" class="btn btn-secondary">Cancel</a>
                      <button type="button" class="btn btn-success" onclick="showConfirm('saveForm')">Save Product</button>
                    </div>
                  </form>     
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
      <!-- Modal -->
      <!-- Coloure Modal (won’t close the other modal) -->
      <div class="modal fade" id="coloureModal" tabindex="-1" aria-labelledby="coloureModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
             {{-- Success message (will be shown by JS) --}}
             <div id="colourSuccess" class="alert alert-success d-none"></div>
            <form id="colourForm" method="POST" action="{{route('coloure.store')}}">
              @csrf
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="colourModalLabel">Add New Colour</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label>Colour Name</label>
                  <input type="text" name="colourName" id="colourName" class="form-control @error('colourName') is-invalid @enderror" autocomplete="off">
                  @error('colourName')
                      <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="modal-footer">
                <div class="col-md-4">
                    <input type="text" id="searchColour" class="form-control form-control-sm" placeholder="Search by Colour">
                </div>
                <button  type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
              <div class="card">
                <div class="card-body p-2">
                    <!-- Scrollable container -->
                    <div style="max-height: 250px; min-height: 100px; overflow-y: auto;">
                        <table class="table table-bordered table-sm mb-0" id="colourTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 5%;">No.</th>
                                    <th style="width: 70%;">Colour</th>
                                    <th style="width: 25%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                              @forelse($colours as $colour)
                                <tr>
                                    <td>{{ $colour->id }}</td>
                                    <td>{{ $colour->colourName }}</td>
                                  <td>
                                    <form action="{{ route('coloure.destroy', $colour->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this colour?')">
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
      <!-- Paking Modal (won’t close the other modal) -->
      <div class="modal fade" id="pakingModal" tabindex="-1" aria-labelledby="pakingModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form id="pakingForm" method="POST" action="{{route('packing.store')}}">
               @csrf
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="areaModalLabel">Add New Paking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label>Paking Name</label>
                  <input type="text" name="pakingName" id="pakingName" class="form-control" autocomplete="off">
                </div>
              </div>
              <div class="modal-footer">
                <div class="col-md-4">
                    <input type="text" id="searchPacking" class="form-control form-control-sm" placeholder="Search by Packing">
                </div>
                <button type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
              <div class="card">
                <div class="card-body p-2">
                    <!-- Scrollable container -->
                    <div style="max-height: 250px; min-height: 100px; overflow-y: auto;">
                        <table class="table table-bordered table-sm mb-0" id="pakingTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 5%;">No.</th>
                                    <th style="width: 70%;">Packing</th>
                                    <th style="width: 25%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packings as $packing)
                              <tr>
                                  <td>{{ $packing->id }}</td>
                                  <td>{{ $packing->pakingName }}</td>
                                  <td>
                                      <form action="{{ route('packing.destroy', $packing->id) }}" method="POST" style="display:inline;">
                                          @csrf
                                          @method('DELETE')
                                          <button type="submit" class="btn btn-sm btn-danger"
                                              onclick="return confirm('Are you sure you want to delete this catagory?')">
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
      <!-- Modal -->
      <!-- Catagory Modal (won’t close the other modal) -->
      <div class="modal fade" id="catagoryModal" tabindex="-1" aria-labelledby="catagoryModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <form id="catagoryForm" method="POST" action="{{route('catagory.store')}}">
              @csrf
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="catagoryModalLabel">Add New Catagory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label>Catagory Name</label>
                  <input type="text" name="catagoryName" id="catagoryName" class="form-control" autocomplete="off">
                </div>
              </div>
              <div class="modal-footer">
                <div class="col-md-4">
                    <input type="text" id="searchCatagory" class="form-control form-control-sm" placeholder="Search by Catagory">
                </div>
                <button type="submit" class="btn btn-success">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
            <div class="card">
              <div class="card-body p-2">
                  <!-- Scrollable container -->
                  <div style="max-height: 250px; min-height: 100px; overflow-y: auto;">
                      <table class="table table-bordered table-sm mb-0" id="catagoryTable">
                          <thead class="table-light sticky-top">
                              <tr>
                                  <th style="width: 5%;">No.</th>
                                  <th style="width: 70%;">Colour</th>
                                  <th style="width: 25%;">Action</th>
                              </tr>
                          </thead>
                          <tbody>
                           @foreach($catagorys as $catagor)
                            <tr>
                                <td>{{ $catagor->id }}</td>
                                <td>{{ $catagor->catagoryName }}</td>
                                <td>
                                    <form action="{{ route('catagory.destroy', $catagor->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this catagory?')">
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
      <!-- Modal -->
      <!-- company Modal (won’t close the other modal) -->
      <!-- Company Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="companyForm" method="POST" class="modal-form" action="{{route('company.store')}}">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Add New Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Company Name</label>
                        <input type="text" name="companyName" id='companyName' class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-md-4">
                        <input type="text" id="searchCompany" class="form-control form-control-sm" placeholder="Search by Company">
                    </div>
                    <button type="submit" class="btn btn-success">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
            <div class="card">
                <div class="card-body p-2">
                    <div style="max-height: 250px; min-height: 100px; overflow-y: auto;">
                        <table class="table table-bordered table-sm mb-0" id="companyTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 5%;">No.</th>
                                    <th style="width: 70%;">Company</th>
                                    <th style="width: 25%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                              @foreach($proCompanys as $company)
                              <tr>
                                  <td>{{ $company->id }}</td>
                                  <td>{{ $company->companyName }}</td>
                                  <td>
                                      <form action="{{ route('company.destroy', $company->id) }}" method="POST" style="display:inline;">
                                          @csrf
                                          @method('DELETE')
                                          <button type="submit" class="btn btn-sm btn-danger"
                                             onclick="confirmDelete('deleteForm{{ $company->id }}', 'Delete Customer', 'Do you really want to delete {{ $company->name }}?')">
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
  document.addEventListener("DOMContentLoaded", function () {
    const searchConfigs = [
      { input: "searchColour", table: "colourTable" },
      { input: "searchPacking", table: "pakingTable" },
      { input: "searchCatagory", table: "catagoryTable" },
      { input: "searchCompany", table: "companyTable" },
      { input: "searchName", table: "productTable", col: 1 },
      { input: "searchArea", table: "productTable", col: 2 }
    ];

    searchConfigs.forEach(({ input, table, col }) => {
      const inputEl = document.getElementById(input);
      const tbody = document.getElementById(table)?.querySelector("tbody");
      if (!inputEl || !tbody) return;

      inputEl.addEventListener("input", () => {
        const val = inputEl.value.toLowerCase();
        Array.from(tbody.rows).forEach(row => {
          const text = row.cells[col ?? 1].textContent.toLowerCase();
          row.style.display = text.includes(val) ? "" : "none";
        });
      });
    });

   

    // Re-initialize Select2 on modal show
    $('#productModal').on('shown.bs.modal', function () {
      ['#CategorySelect', '#pakingSelect', '#colourSelect', '#companySelect'].forEach(id => {
        $(id).select2({
          dropdownParent: $('#productModal'),
          placeholder: "Choose Option",
          allowClear: true
        });
      });
    });
  });

  // General modal openers
  window.openModal = function (id) {
      new bootstrap.Modal(document.getElementById(id), {
          backdrop: 'static',
          keyboard: false
      }).show();
      // Load data when modal opens
      loadTableData(id.replace('Modal', ''));
  };

  
function handleModalForm(formId, selectId, modalId, keyName, valueField) {
    $('#' + formId).submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    // Use the returned object keyName and valueField
                    let item = res[keyName];
                    let value = item[valueField]; // e.g., item.companyName
                    $('#' + selectId).append(
                        new Option(value, value, true, true)
                    ).trigger('change');
                    
                    $('#' + modalId).modal('hide');
                    $('#' + formId)[0].reset();
                }
            },
            error: function(xhr){
                console.log(xhr.responseJSON);
            }
        });
    });
}

// Initialize for all modals with proper field names
handleModalForm('companyForm', 'companySelect', 'companyModal', 'company', 'companyName');
handleModalForm('colourForm', 'colourSelect', 'coloureModal', 'colour', 'colourName');
handleModalForm('pakingForm', 'packingSelect', 'pakingModal', 'packing', 'pakingName');
handleModalForm('catagoryForm', 'CategorySelect', 'catagoryModal', 'catagory', 'catagoryName');
   
function modalForm(formId, selectId){
    $('#' + formId).submit(function(e){
        e.preventDefault();
        let f = $(this);

        $.post(f.attr('action'), f.serialize())
         .done(res => {
            if(res.success && res[selectId]){
                let data = res[selectId];
                let newId   = data.id;
                let newName = data.colourName; // <- use real field name

                // Prevent duplicate
                if($("#" + selectId + " option[value='"+ newId +"']").length === 0){
                    $('#' + selectId).append(
                        new Option(newName, newId, true, true)
                    ).val(newId).trigger('change'); // also select it
                }

                f[0].reset();
                f.find('.is-invalid').removeClass('is-invalid');
                f.find('.invalid-feedback').remove();
                $('#' + formId.replace('Form','Modal')).modal('hide');
            }
        })

         .fail(xhr => {
            let errors = xhr.responseJSON.errors;
            f.find('.is-invalid').removeClass('is-invalid'); 
            f.find('.invalid-feedback').remove();

            $.each(errors, (k,v) => {
                f.find('[name="'+k+'"]').addClass('is-invalid')
                 .after('<div class="invalid-feedback">'+v[0]+'</div>');
            });
        });
    });
}


// Initialize all forms
modalForm('companyForm','companyName');
modalForm('colourForm','colourName');
modalForm('pakingForm','pakingName');
modalForm('catagoryForm','catagoryName');


</script>


      @endsection
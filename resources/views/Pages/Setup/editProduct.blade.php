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
              <div class="col-sm-6"><h3 class="mb-0">Edit Product</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item"><a href="{{ url('/product') }}">Product</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Edit Product</li>
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
                    <form action="{{ route('product.update', $product->id) }}" method="POST" id="saveForm">
                        @csrf
                        {{-- if using PUT/PATCH --}}
                        @method('POST') {{-- or PUT if you want --}}
                        <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                            <label class="form-label">Product Code</label>
                            <input type="text" class="form-control" name="productCode" value="{{ $product->productCode }}" readonly>
                            </div>

                            <div class="col-md-9">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="productName"  value="{{ $product->productName }}" autocomplete="off">
                            </div>

                            <div class="col-md-4">
                              <label class="form-label">Product Company</label>
                              <select class="form-select select2 w-100" name="companyName" id="companySelect">
                                <option disabled {{ old('companyName', $product->companyName ?? '') == '' ? 'selected' : '' }}>
                                    Choose Company
                                </option>  
                                @foreach($proCompanys as $company)
                                    <option value="{{ $company->companyName }}" 
                                        {{ old('companyName', $product->companyName ?? '') == $company->companyName ? 'selected' : '' }}>
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
                            <select class="form-select select2 w-100" name="colourName"  id="colourSelect" style="">
                              <option disabled {{ old('colourName', $product->colourName ?? '')== '' ? 'selected' : '' }}>Choose Colour</option>
                              @foreach($colours as $colour)
                                <option value="{{ $colour->colourName }}" 
                                    {{ old('colourName', $product->colourName ?? '') == $colour->colourName ? 'selected' : '' }}>
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
                              <select class="form-select select2" name="pakingName" id="packingSelect">
                                  <option disabled {{ old('pakingName', $product->pakingName ?? '')== '' ? 'selected' : '' }}>Choose Packing</option>
                                  @foreach($packings as $packing)
                                      <option value="{{ $packing->pakingName }}" 
                                          {{ old('pakingName', $product->pakingName ?? '') == $packing->pakingName ? 'selected' : '' }}>
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
                            <input type="number" step="0.01" class="form-control" name="saleRate" value="{{ $product->saleRate }}" required autocomplete="off">
                            </div>

                            <div class="col-md-4">
                            <label class="form-label">Cost Rate</label>
                            <input type="number" step="0.01" class="form-control" name="costRate" value="{{ $product->costRate }}" autocomplete="off" required>
                            </div>

                            <div class="col-md-4">
                            <label class="form-label">Less Quantity</label>
                            <input type="number" step="0.01" class="form-control" name="lessQty" value="{{ $product->lessQty }}" autocomplete="off" required>
                            </div>

                            <div class="col-md-4">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" step="0.01" class="form-control" name="stockQty" value="{{ $product->stockQty }}" autocomplete="off" required>
                            </div>

                            <div class="col-md-4">
                            <label class="form-label">Category</label>
                              <select class="form-select select2" name="catagoryName" id="CategorySelect">
                                <option disabled {{ old('catagoryName', $product->catagoryName ?? '')== '' ? 'selected' : '' }}>Choose Catagory</option>
                                @foreach($catagorys as $catagory)
                                    <option value="{{ $catagory->catagoryName }}" 
                                        {{ old('catagoryName', $product->catagoryName ?? '') == $catagory->catagoryName ? 'selected' : '' }}>
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
      
      <main class="container mt-5">
        
        </div>
      </main>
      
    <script>
  document.addEventListener("DOMContentLoaded", function () {
    const searchConfigs = [
      { input: "searchColour", table: "colourTable" },
      { input: "searchPacking", table: "pakingTable" },
      { input: "searchCatagory", table: "catagoryTable" },
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

    // General modal openers
    window.openModal = function (id) {
      new bootstrap.Modal(document.getElementById(id), {
        backdrop: 'static',
        keyboard: false
      }).show();
    };

    // Re-initialize Select2 on modal show
    $('#productModal').on('shown.bs.modal', function () {
      ['#CategorySelect', '#pakingSelect', '#colourSelect'].forEach(id => {
        $(id).select2({
          dropdownParent: $('#productModal'),
          placeholder: "Choose Option",
          allowClear: true
        });
      });
    });
  });
</script>


      @endsection
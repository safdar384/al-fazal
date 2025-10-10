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
              <div class="col-sm-6"><h3 class="mb-0">Product Information</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Product Info</li>
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
                <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center ">
                        <div class="col-md-2">
                            <input type="text" id="searchCode" class="form-control form-control-sm" autocomplete="off" placeholder="Search by Code">
                        </div>
                         <div class="col-md-2">
                            <input type="text" id="searchCompany" class="form-control form-control-sm" autocomplete="off" placeholder="Search by Company">
                        </div>
                        <div>
                        <button type="button" class="btn btn-primary" onclick="window.location.href='{{route('product.create')}}'">
                          Add New Product
                        </button>
                        </div>
                    </div>

                    <div class="card-body table-wrapper">
                    <table class="table table-bordered table-hover" id="productTable">
                        <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Code </th>
                            <th>Product Name </th>
                            <th>Company </th>
                            <th>Colour </th>
                            <th>Paking</th>
                            <th>Sale Price</th>
                            <th>Cost Price</th>
                            <th>Qty</th>
                            <th>Action</th>
                        </tr>

                        </thead>
                        <tbody>
                          @foreach($products as $product)
                          <tr>
                              <td>{{ $product->id }}</td>
                              <td>{{ $product->productCode }}</td>
                              <td>{{ $product->productName }}</td>
                              <td>{{ $product->companyName }}</td>
                              <td>{{ $product->colourName }}</td>
                              <td>{{ $product->pakingName }}</td>
                              <td>{{ $product->saleRate }}</td>
                              <td>{{ $product->costRate }}</td>
                              <td>{{ $product->stockQty }}</td>
                              <td>
                              <button class="btn btn-sm btn-warning" onclick="window.location.href='{{ route('product.edit', $product->id) }}'">Edit</button>

                              <form id="deleteForm{{ $product->id }}" action="{{ route('product.destroy', $product->id) }}" method="POST" style="display:inline;">
                                  @csrf
                                  @method('DELETE')
                                  <button type="button" class="btn btn-sm btn-danger" onclick="showConfirm({
                                          formId: 'deleteForm{{ $product->id }}',
                                          title: 'Confirm Delete',
                                          message: 'Are you sure you want to delete this Code {{ $product->productCode }}',
                                          okText: 'Yes, Delete',
                                          okClass: 'btn-danger',
                                          icon: 'bi bi-trash'
                                      })">Delete</button>
                              </form>
                              </td>
                          </tr>
                          @endforeach 
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
      

    <script>
$(document).ready(function () {
    // Initialize DataTable
    var table = $('#productTable').DataTable({
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50],
        "paging": true,
        "info": true,
        "searching": true, // enable searching
        "ordering": true
        
    });

    // Custom search inputs
    $('#searchCode').on('keyup', function () {
        table.column(1).search(this.value).draw(); // Product Name (col index starts at 0)
    });

    $('#searchCompany').on('keyup', function () {
        table.column(3).search(this.value).draw(); // Company
    });
});




</script>


      @endsection
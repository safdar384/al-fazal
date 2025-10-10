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
              <div class="col-sm-6"><h3 class="mb-0">Customer</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Customer</li>
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
                            <input type="text" id="areaSearch" class="form-control form-control-sm" placeholder="Search by Area Code" autocomplete="off">
                        </div>
                         <div class="col-md-2">
                            <input type="text" id="searchCustomer" class="form-control form-control-sm" placeholder="Search by Customer Code" autocomplete="off">
                        </div>
                        <div>
                        <button type="button" class="btn btn-primary" onclick="window.location.href='{{ route('Customer.create') }}'">
                            Add New Customer
                        </button>
                        </div>
                    </div>

                    <div class="card-body table-wrapper">
                    <table class="table table-bordered table-hover" id="customerTable">
                        <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Area Code </th>
                            <th>Cust Code </th>
                            <th>Party Name </th>
                            <th>City </th>
                            <th>Cell No</th>
                            <th>Balance</th>
                            <th>Action</th>
                        </tr>

                        </thead>
                        <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->custArea }}</td>
                            <td>{{ $customer->custCode }}</td>
                            <td>{{ $customer->bussnessName }}</td>
                            <td>{{ $customer->custCity }}</td>
                            <td>{{ $customer->custContect }}</td>
                            <td>Rs. {{ number_format($customer->custBalance) }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="window.location.href='{{route('Customer.edit', $customer->id) }}'">Edit</button>

                                <form id="deleteForm{{ $customer->id }}" action="{{ route('Customer.destroy', $customer->id) }}" method="POST" style="display:inline;">
                                  @csrf
                                  @method('DELETE')
                                  
                                  <button type="button" class="btn btn-sm btn-danger" onclick="showConfirm({
                                          formId: 'deleteForm{{ $customer->id }}',
                                          title: 'Confirm Delete',
                                          message: 'Are you sure you want to delete {{ $customer->bussnessName }}',
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
          var table = $('#customerTable').DataTable({
              "pageLength": 5,
              "lengthMenu": [5, 10, 25, 50],
              "paging": true,
              "info": true,
              "searching": true, // enable searching
              "ordering": true
              
          });

          // Custom search inputs
          $('#areaSearch').on('keyup', function () {
              table.column(1).search(this.value).draw(); // Area Name (col index starts at 0)
          });

          $('#searchCustomer').on('keyup', function () {
              table.column(2).search(this.value).draw(); // Customer Name
          });
      });

    </script>

      @endsection
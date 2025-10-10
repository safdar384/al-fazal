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
              <div class="col-sm-6"><h3 class="mb-0">Ajent Information</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Ajent</li>
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
                            <input type="text" id="ajentSearch" class="form-control form-control-sm" placeholder="Search by ajent Name">
                        </div>
                        <div>
                        <button type="button" class="btn btn-primary" onclick="window.location.href='{{ route('NewAjent.create') }}'">
                            Add New Ajent
                        </button>
                        </div>
                    </div>

                    <div class="card-body table-wrapper">
                    <table class="table table-bordered table-hover" id="ajentTable">
                        <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Ajent Code</th>
                            <th>Ajent Name </th>
                            <th>Cell No</th>
                            <th>Advance</th>
                            <th>Balance</th>
                            <th>Action</th>
                        </tr>

                        </thead>
                        <tbody>
                        @foreach($ajents as $ajent)
                        <tr>
                            <td>{{ $ajent->id }}</td>
                            <td>{{ $ajent->ajentCode }}</td>
                            <td>{{ $ajent->ajentName }}</td>
                            <td>{{ $ajent->ajentContact }}</td>
                            <td>{{ $ajent->AjentAdvance }}</td>
                            <td>Rs. {{ number_format($ajent->ajentBalance) }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="window.location.href='{{route('NewAjent.edit', $ajent->id) }}'">Edit</button>

                                <form id="deleteForm{{ $ajent->id }}" action="{{ route('NewAjent.destroy', $ajent->id) }}" method="POST" style="display:inline;">
                                  @csrf
                                  @method('DELETE')
                                  <button type="button" class="btn btn-sm btn-danger" 
                                  onclick="confirmDelete('deleteForm{{ $ajent->id }}', 'Delete Ajent', 'Do you really want to delete {{ $ajent->name }}?')">
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
  document.addEventListener("DOMContentLoaded", function () {
    const ajentInput = document.getElementById("ajentSearch");
    const table = document.getElementById("ajentTable").getElementsByTagName("tbody")[0];

    function filterTable() {
      const ajentVal = ajentInput.value.toLowerCase();

      Array.from(table.rows).forEach(row => {
        const ajentText = row.cells[1].textContent.toLowerCase(); // Ajent Name column
        const match = ajentText.includes(ajentVal);
        row.style.display = match ? "" : "none";
      });
    }

    ajentInput.addEventListener("input", filterTable);
  });
</script>

      @endsection
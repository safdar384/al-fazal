@extends('layout.app')

@section('title', 'Dashboard')

@section('content')

<main class="app-main" id="main" tabindex="-1">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Bank Account</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Bank</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-12">
                <div class="container-fluid">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center ">
                        <div class="col-md-2">
                            <input type="text" id="bankSearch" class="form-control form-control-sm" placeholder="Search by Bank Code" autocomplete="off">
                        </div>
                        <div>
                          <button type="button" class="btn btn-primary" onclick="openbankModal()">
                              Add New Bank
                          </button>
                        </div>
                    </div>

                    <div class="card-body table-wrapper">
                    <table class="table table-bordered table-hover" id="customerTable">
                        <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Bank Account </th>
                            <th>Bank Name </th>
                            <th>Bank Address </th>
                            <th>Branch Code</th>
                            <th>Balance</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                           @foreach($bankAC as $bank)
                        <tr>
                            <td>{{ $bank->id }}</td>
                            <td>{{ $bank->bankAccount }}</td>
                            <td>{{ $bank->bankName }}</td>
                            <td>{{ $bank->bankAddress }}</td>
                            <td>{{ $bank->branchCode }}</td>
                            <td>Rs. {{ number_format($bank->balance) }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="openeditbankModal({{ $bank->id }})">Edit</button>

                                <form id="deleteForm{{ $bank->id }}" action="{{ route('bankAccount.destroy', $bank->id) }}" method="POST" style="display:inline;">
                                  @csrf
                                  @method('DELETE')
                                  <button type="button" class="btn btn-sm btn-danger" 
                                  onclick="confirmDelete('deleteForm{{ $bank->id }}', 'Delete Customer', 'Do you really want to delete {{ $bank->name }}?')">
                                      Delete
                                  </button>
                              </form>
                            </td>
                        </tr>

                        <!-- Edit Bank Modal for each record -->
                        <div class="modal fade" id="editBankModal{{ $bank->id }}" tabindex="-1" aria-labelledby="enditbankModalLabel" aria-hidden="true" data-bs-backdrop="static">
                          <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('bankAccount.update', $bank->id) }}" method="POST">
                                @csrf
                                 @method('PUT')
                                 <input type="hidden" name="form_type" value="edit">
                                 <input type="hidden" name="bank_id" value="{{ $bank->id }}">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Edit Bank</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                  <div class="mb-2">
                                    <label>Bank Account</label>
                                    <input type="text" name="bankAccount" class="form-control form-control-sm" value="{{ old('bankAccount', $bank->bankAccount) }}" autocomplete="off">
                                    @error('bankAccount')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                  </div>
                                  <div class="mb-2">
                                    <label>Bank Name</label>
                                    <input type="text" name="bankName" class="form-control form-control-sm" value="{{ old('bankName', $bank->bankName) }}" autocomplete="off">
                                    @error('bankName')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                  </div>
                                  <div class="mb-2">
                                    <label>Bank Address</label>
                                    <input type="text" name="bankAddress" class="form-control form-control-sm" value="{{ old('bankAddress', $bank->bankAddress) }}" autocomplete="off">
                                    @error('bankAddress')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                  </div>
                                  <div class="mb-2">
                                    <label>Branch Code</label>
                                    <input type="text" name="branchCode" class="form-control form-control-sm" value="{{ old('branchCode', $bank->branchCode) }}" autocomplete="off">
                                    @error('branchCode')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                  </div>
                                  <div class="mb-2">
                                    <label>Balance</label>
                                    <input type="number" step="0.00" name="balance" class="form-control form-control-sm" value="{{ old('balance', $bank->balance) }}" autocomplete="off">
                                    @error('balance')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                  </div>
                                </div>
                                <div class="modal-footer">
                                  <button type="submit" class="btn btn-success btn-sm">Save</button>
                                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                </div>
                                </form>
                            </div>
                          </div>
                        </div>
                        @endforeach  
                      </tbody>
                    </table>
                    </div>
                </div>
                </div>
              </div>
            </div>
          </div>
        </div>
</main>

<!-- Add Bank Modal -->
<div class="modal fade" id="bankModal" tabindex="-1" aria-labelledby="bankModalLabel" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <form action="{{ route('bankAccount.store') }}" method="POST" id="bankForm">
        @csrf
        <input type="hidden" name="form_type" value="add">
        <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Add New Bank</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label>Bank Account</label>
            <input type="text" name="bankAccount" class="form-control form-control-sm" value="{{ old('bankAccount') }}" autocomplete="off">
             @error('bankAccount')
                <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="mb-2">
            <label>Bank Name</label>
            <input type="text" name="bankName" class="form-control form-control-sm" value="{{ old('bankName') }}" autocomplete="off">
              @error('bankName')
                  <span class="text-danger">{{ $message }}</span>
              @enderror
          </div>
          <div class="mb-2">
            <label>Bank Address</label>
            <input type="text" name="bankAddress" class="form-control form-control-sm" value="{{ old('bankAddress') }}" autocomplete="off">
              @error('bankAddress')
                  <span class="text-danger">{{ $message }}</span>
              @enderror
          </div>
          <div class="mb-2">
            <label>Branch Code</label>
            <input type="text" name="branchCode" class="form-control form-control-sm" value="{{ old('branchCode') }}" autocomplete="off">
              @error('branchCode')
                  <span class="text-danger">{{ $message }}</span>
              @enderror
          </div>
          <div class="mb-2">
            <label>Balance</label>
            <input type="number" step="0.00" name="balance" class="form-control form-control-sm" value="{{ old('balance') }}" autocomplete="off">
              @error('balance')
                  <span class="text-danger">{{ $message }}</span>
              @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success btn-sm">Save</button>
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        </div>
        </form>
    </div>
  </div>
</div>


<script>
   // Open bank Modal
    function openbankModal() {
        const modal = new bootstrap.Modal(document.getElementById('bankModal'), {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();
    }

    // Open Edit bank Modal
    function openeditbankModal(id) {
      let modalEl = document.getElementById('editBankModal' + id);
      let modal = new bootstrap.Modal(modalEl);
      modal.show();
    }

    $(document).ready(function () {
        var table = $('#customerTable').DataTable({
            "pageLength": 5,
            "lengthMenu": [5, 10, 25, 50],
            "paging": true,
            "info": true,
            "searching": true,
            "ordering": true
        });
    });
</script>

<!-- Keep modal open if validation fails -->
@if ($errors->any())
<script>
document.addEventListener("DOMContentLoaded", function() {
    @if(old('form_type') == 'edit')
        let editId = "{{ old('bank_id') }}";
        if(editId){
            let modalEl = document.getElementById('editBankModal' + editId);
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
    @else
        const addModal = document.getElementById('bankModal');
        if (addModal) {
            const modal = new bootstrap.Modal(addModal);
            modal.show();
        }
    @endif
});
</script>
@endif

@endsection

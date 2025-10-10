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
          <div class="col-sm-6"><h3 class="mb-0">Edit Ajent</h3></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/ajent') }}">Ajent</a></li>
              <li class="breadcrumb-item active" aria-current="page">Edit Ajent</li>
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
        <h5 class="mb-0">Edit Ajent Entry</h5>
      </div>

      <form action="{{ route('NewAjent.update', $editAjent ->id) }}" method="POST">
        @csrf
        @method('PUT') 
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Ajent Code</label>
              <input type="text" class="form-control" name="ajentCode" value="{{ $editAjent->ajentCode }}" autocomplete="off">
            </div>

            <div class="col-md-6">
              <label class="form-label">Ajent Name</label>
              <input type="text" class="form-control" name="ajentName" value="{{ $editAjent->ajentName }}" autocomplete="off">
            </div>

            <div class="col-md-3">
              <label class="form-label">Ajent Advance</label>
              <input type="text" step="0.01" class="form-control " name="AjentAdvance" value="{{ $editAjent->AjentAdvance }}" autocomplete="off">
            </div>

            <div class="col-md-12">
              <label class="form-label">Ajent Address</label>
              <input type="text" class="form-control" name="ajentAdderss" value="{{ $editAjent->ajentAdderss }}" autocomplete="off">
            </div>

            <div class="col-md-3">
              <label class="form-label">Ajent Contact</label>
              <input type="text" class="form-control" name="ajentContact" value="{{ $editAjent->ajentContact }}" autocomplete="off">
            </div>

            <div class="col-md-3">
              <label class="form-label">Ajent Commission (%)</label>
              <input type="number" step="0.01" class="form-control" name="ajentCommission" value="{{ $editAjent->ajentCommission }}" autocomplete="off">
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Ajent Balance</label>
              <input type="number" step="0.01" class="form-control" name="ajentBalance" value="{{ $editAjent->ajentBalance }}" autocomplete="off">
            </div>
    
          </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('NewAjent.index') }}'">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Ajent</button>
        </div>
      </form>
    </div>
  </div>
</main>

      
      @endsection
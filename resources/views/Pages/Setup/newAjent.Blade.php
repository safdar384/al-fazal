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
          <div class="col-sm-6"><h3 class="mb-0">New Ajent</h3></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-end">
              <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
              <li class="breadcrumb-item"><a href="{{ url('/ajent') }}">Ajent</a></li>
              <li class="breadcrumb-item active" aria-current="page">Add New Ajent</li>
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
        <h5 class="mb-0">New Ajent Entry</h5>
      </div>

      <form action="{{ route('NewAjent.store') }}" method="POST">
       @csrf 
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Ajent Code</label>
              <input type="text" class="form-control" name="ajentCode" value="{{ old('ajentCode') }}" autocomplete="off">
              @error('ajentCode')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-md-6">
              <label class="form-label">Ajent Name</label>
              <input type="text" class="form-control" name="ajentName" value="{{ old('ajentName') }}"autocomplete="off">
              @error('ajentName')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Ajent Advance</label>
              <input type="text" step="0.01" class="form-control " name="AjentAdvance" value="{{ old('AjentAdvance') }}" autocomplete="off">
              @error('AjentAdvance')
                <span class="text-danger">{{ $message }}</span> 
              @enderror
            </div>

            <div class="col-md-12">
              <label class="form-label">Ajent Address</label>
              <input type="text" class="form-control" name="ajentAdderss" value="{{ old('ajentAdderss') }}" autocomplete="off">
              @error('ajentAdderss')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Ajent Contact</label>
              <input type="text" class="form-control" name="ajentContact" value="{{ old('ajentContact') }}" autocomplete="off">
              @error('ajentContact')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Ajent Commission (%)</label>
              <input type="number" step="0.01" class="form-control" name="ajentCommission" value="{{ old('ajentCommission') }}" autocomplete="off">
              @error('ajentCommission')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            
            <div class="col-md-3">
              <label class="form-label">Ajent Balance</label>
              <input type="number" step="0.01" class="form-control" name="ajentBalance" value="{{ old('ajentBalance') }}" autocomplete="off">
              @error('ajentBalance')
                <span class="text-danger">{{ $message }}</span>
              @enderror
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
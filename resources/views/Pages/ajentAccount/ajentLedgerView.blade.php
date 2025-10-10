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
              <div class="col-sm-6"><h3 class="mb-0">Ajent Ledger View</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Ajent Ledger</li>
                </ol>
              </div>
              <hr style="border-top: 1px solid #140097; md-3">
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
                    <div class="row mb-3">
                      <div class="col-md-2">
                        <label class="form-label mb-1">Select Ajent Account</label>
                        <select class="form-select select2 form-select-sm" name="ajentName" id="slectAjent">
                          <option selected disabled>Choose Ajent</option>
                          <option value="Retail">BAN</option>
                          <option value="Wholesale">MGP</option>
                          <option value="VIP">JNP</option>
                        </select>
                      </div>
                      <div class="col-md-4">
                        <label class="form-label mb-1">Name</label>
                        <input class="form-control form-control-sm" type="text" name="name" placeholder="" disabled>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label mb-1">Balance</label>
                        <input class="form-control form-control-sm" step="0.01" type="number" name="Balance" placeholder="" disabled>
                      </div>
                      <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="window.location.href=''">
                            Ledger View
                        </button>
                      </div>
                    </div>
                     <div class="card">
                      <div class="card-body table-wrapper">
                        <table class="table table-bordered table-hover" id="ajentTable">
                          <thead class="table-primary">
                          <tr>
                              <th>ID</th>
                              <th>Ajent Code </th>
                              <th>Discription </th>
                              <th>Balance</th>
                              <th>Recovery</th>
                              <th>Recovery%</th>
                              <th>Comm%</th>
                              <th>Credit</th>
                              <th>Debit</th>
                              <th>Action</th>
                          </tr>

                          </thead>
                          <tbody>
                          <tr>
                              <td>1</td>
                              <td>IMR_MRK</td>
                              <td>JNP Market Recovery</td>
                              <td>1025650</td>
                              <td>500000</td>
                              <td>12.20</td>
                              <td>8</td>
                              <td>0</td>
                              <td>5000</td>
                              <td>
                              <button class="btn btn-sm btn-warning" onclick="window.location.href='{{ url('/EditAjentAccountTransection') }}'">Edit</button>
                              <button class="btn btn-sm btn-danger">Delete</button>
                              </td>
                          </tr>
                          <tr>
                              <td>2</td>
                              <td>IMR_MRK</td>
                              <td>BAN Market Recovery</td>
                              <td>1025650</td>
                              <td>500000</td>
                              <td>12.20</td>
                              <td>8</td>
                              <td>5000</td>
                              <td>0</td>
                              <td>
                              <button class="btn btn-sm btn-warning">Edit</button>
                              <button class="btn btn-sm btn-danger">Delete</button>
                              </td>
                          </tr>
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


      @endsection
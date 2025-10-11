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
              <div class="col-sm-6"><h3 class="mb-0">Edit Agent Account Transection</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item"><a href="{{ url('/AgentAccountTransection') }}">Agent Account</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Edit Agent Account</li>
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
                        <label class="form-label mb-1">Select Agent Account</label>
                        <select class="form-select select2 form-select-sm" name="agentName" id="slectAgent">
                          <option selected disabled>Choose Agent</option>
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
                      <div class="col-md-2">
                        <label class="form-label mb-1">total Balance</label>
                        <input class="form-control form-control-sm" step="0.01" type="number" name="totalBalance" placeholder="" disabled>
                      </div>
                    </div>
                     <div class="card">
                      <div class="card-body table-wrapper">
                        <div class="row g-2 mb-2">
                          <div class="col-md-3">
                            <label class="form-label mb-1">Discription</label>
                            <input type="text" class="form-control form-control-sm" name="Discription" required>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label mb-1">Market Balance</label>
                            <input type="number" class="form-control form-control-sm" step="0.01" name="mrkBalance" required>
                          </div>
                          <div class="col-md-1">
                            <label class="form-label mb-1">Recovery</label>
                            <input type="number" class="form-control form-control-sm" step="0.01" name="recovery" required>
                          </div>
                          <div class="col-md-1">
                            <label class="form-label mb-1">Recovery %</label>
                            <input type="number" class="form-control form-control-sm" step="0.01" name="recoveryPrecent" required>
                          </div>
                          <div class="col-md-1">
                            <label class="form-label mb-1">Comm %</label>
                            <input type="number" class="form-control form-control-sm" step="0.01" name="commPercent" required>
                          </div>
                          <div class="col-md-1">
                            <label class="form-label mb-1">Amount</label>
                            <input type="number" class="form-control form-control-sm" step="0.01" name="amount" required>
                          </div>
                          <div class="col-md-2">
                              <div class="form-check mt-1">
                              <input class="form-check-input" type="checkbox" value="otherLedger" id="flexCheckDefault" checked>
                              <label class="form-check-label" for="flexCheckDefault">
                                Entry In Other Accouont
                              </label>
                            </div>
                            <select class="form-select select2 form-select-sm" name="CelectCatagory" id="AgentCategorySelect">
                              <option selected disabled>Chose</option>
                              <option value="Retail">Credit</option>
                              <option value="Wholesale">Debit</option>
                            </select>                            
                          </div>
                          <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-primary btn-sm w-100" onclick="window.location.href=''">
                              Save
                            </button>
                          </div>
                        </div>

                        <table class="table table-bordered table-hover" id="agentTable">
                          <thead class="table-primary">
                          <tr>
                              <th>ID</th>
                              <th>Agent Code </th>
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
                              <button class="btn btn-sm btn-warning" onclick="window.location.href='{{ url('/editAgent') }}'">Edit</button>
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
      
     <script>
  

      @endsection
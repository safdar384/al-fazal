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
              <div class="col-sm-6"><h3 class="mb-0">Customer Ledger Report</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Customer Ledger</li>
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
                    <div class="row g-2 mb-2">
                      <div class="col-md-2">
                        <label class="form-label mb-1">Select Customer Area</label>
                        <select class="form-select select2 form-select-sm" name="custArea" id="slectArea">
                          <option selected disabled>Choose Area</option>
                          <option value="Retail">BAN</option>
                          <option value="Wholesale">MGP</option>
                          <option value="VIP">JNP</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1">Select Customer Code</label>
                        <select class="form-select select2 form-select-sm" name="customerCode" id="slectCustomerCode">
                        <option selected disabled>Choose Code</option>
                        <option value="Retail">BAN</option>
                        <option value="Wholesale">MGP</option>
                        <option value="VIP">JNP</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1">Customer Name</label>
                        <input class="form-control form-control-sm" type="text" name="customerName" placeholder="" disabled>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label mb-1">Pre Balance</label>
                        <input class="form-control form-control-sm" step="0.01" value="100000" type="number" name="preBalance" placeholder="" disabled>
                    </div>
                    </div>
                     <div class="card">
                      <div class="card-body table-wrapper">
                        <table class="table table-bordered table-hover" id="agentTable">
                          <thead class="table-primary">
                          <tr>
                              <th>ID</th>
                              <th>Area Code </th>
                              <th>Customer Code </th>
                              <th>Customer Name</th>
                              <th>Customer City</th>
                              <th>Credit</th>
                              <th>Debit</th>
                              <th>Amount</th>
                              <th>Action</th>
                          </tr>

                          </thead>
                          <tbody>
                          <tr>
                              <td>1</td>
                              <td>BAN</td>
                              <td>SRD</td>
                              <td>Sarhad Electric Stock </td>
                              <td>Kohat</td>
                              <td>35000</td>
                              <td>20000</td>
                              <td>14400</td>
                              <td>
                              <button class="btn btn-sm btn-warning" onclick="window.location.href='{{ url('/EditAgentAccountTransection') }}'">Edit</button>
                              <button class="btn btn-sm btn-danger">Delete</button>
                              </td>
                          </tr>
                          <tr>
                              <td>2</td>
                              <td>BAN</td>
                              <td>SHK</td>
                              <td>Shokat Electric Stock </td>
                              <td>Kohat</td>
                              <td>35000</td>
                              <td>20000</td>
                              <td>14400</td>
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
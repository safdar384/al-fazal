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
              <div class="col-sm-6"><h3 class="mb-0">Customer Recovery Transection</h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Customer Recovery</li>
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
                      <div class="col-md-4">
                        <label class="form-label mb-1">Customer Name</label>
                        <input class="form-control form-control-sm" type="text" name="customerName" placeholder="" disabled>
                      </div>
                      <div class="col-md-1">
                        <label class="form-label mb-1">Pre Balance</label>
                        <input class="form-control form-control-sm" step="0.01" value="100000" type="number" name="preBalance" placeholder="" disabled>
                      </div>
                      <div class="col-md-1">
                        <label class="form-label mb-1">Balance 1</label>
                        <input class="form-control form-control-sm" step="0.01" type="number" name="Balance1" placeholder="" disabled>
                      </div>
                      <div class="col-md-1">
                        <label class="form-label mb-1">Balance 2</label>
                        <input class="form-control form-control-sm" step="0.01" type="number" name="Balance2" placeholder="" disabled>
                      </div>
                      <div class="col-md-1">
                        <label class="form-label mb-1">Last Balance</label>
                        <input class="form-control form-control-sm" step="0.01" type="number" name="lastBalance" placeholder="" disabled>
                      </div>
                      <div class="col-md-2">
                        <label class="form-label mb-1">Recovery Balance</label>
                        <input class="form-control form-control-sm text-primary" type="text" name="recoveryBalance" placeholder="" >
                      </div>
                    </div>
                     <div class="card">
                      <div class="card-body table-wrapper">
                        <div class="row g-2 mb-2">
                          <div class="col-md-2">
                            <label class="form-label mb-1">Select Customer Code</label>
                            <select class="form-select select2 form-select-sm" name="customerCode" id="slectCustomerCode">
                            <option selected disabled>Choose Code</option>
                            <option value="Retail">BAN</option>
                            <option value="Wholesale">MGP</option>
                            <option value="VIP">JNP</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label mb-1">Discription</label>
                            <input type="text" class="form-control form-control-sm" name="Discription" required>
                          </div>
                          <div class="col-md-2">
                            <label class="form-label mb-1">Recovery</label>
                            <input type="number" class="form-control form-control-sm" id="marketRecovery" name="marketRecovery">
                          </div>
                          <div class="col-md-2">
                            <label class="form-label mb-1">Voucher Discount</label>
                            <input type="number" class="form-control form-control-sm" id="voucherDiscount" name="voucherDiscount" value="0">
                          </div>
                          <div class="col-md-2">
                            <label class="form-label mb-1">Bilty or Misc</label>
                            <input type="text" class="form-control form-control-sm" id="biltyMisc" name="biltyMisc" placeholder="Bilty or Misc">
                          </div>
                          <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-primary btn-sm w-100" onclick="window.location.href=''">
                              Save
                            </button>
                          </div>
                        </div>

                        <table class="table table-bordered table-hover" id="ajentTable">
                          <thead class="table-primary">
                          <tr>
                              <th>ID</th>
                              <th>Area Code </th>
                              <th>Customer Code </th>
                              <th>Customer Name</th>
                              <th>Customer City</th>
                              <th>Pre Balance</th>
                              <th>Recovery</th>
                              <th>Discount</th>
                              <th>Misc</th>
                              <th>Last Blance</th>
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
                              <td>500</td>
                              <td>100</td>
                              <td>14400</td>
                              <td>
                              <button class="btn btn-sm btn-warning" onclick="window.location.href='{{ url('/EditAjentAccountTransection') }}'">Edit</button>
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
                              <td>500</td>
                              <td>100</td>
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
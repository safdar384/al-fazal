@extends('layout.app')

@section('title', 'Dashboard')

@section('content')

<div class="container mt-4">
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6"><h3 class="mb-0">Sale Invoice </h3></div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Sale Invoice</li>
                </ol>
              </div>
              <hr style="border-top: 1px solid #140097; md-3">
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
    <div class="card shadow rounded-4">
        <div class="card-header rounded-top-4">
            <h5 class="mb-0">Sale Invoice</h5>
        </div>
        <div class="card-body">
            <!-- Top Section -->
            <div class="container mt-2">
    <div class="row mb-2">

        <!-- Right Side (AreaCode & CustomerCode) -->
        <div class="col-md-2">
            <div class="row">
                <div class="col-12 mb-2">
                    <label class="form-label px-2">Area Code</label>
                    <div class="dropdown w-100">
                        <input type="text" class="form-control search-box form-control-sm" data-type="area" placeholder="Search Area Code">
                        <ul class="dropdown-menu w-auto"></ul>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label px-2">Customer Code</label>
                    <div class="dropdown w-100">
                        <input type="text" class="form-control search-box form-control-sm" data-type="customer" placeholder="Search Customer Code">
                        <ul class="dropdown-menu w-auto"></ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center (AreaName, CustomerName, City, Balance) -->
        <div class="col-md-8 mb-3">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label px-2">Area Name</label>
                    <input type="text" class="form-control form-control-sm" id="areaName" placeholder="Enter Area Name" disabled>
                </div>
                <div class="col-md-8 mb-2">
                    <label class="form-label px-2">Customer Name</label>
                    <input type="text" class="form-control form-control-sm" id="customerName" placeholder="Enter Customer Name" disabled>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label px-2">City</label>
                    <input type="text" class="form-control form-control-sm" id="city" placeholder="Enter City" disabled>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label px-2">Balance</label>
                    <input type="text" class="form-control form-control-sm" id="balance" placeholder="0.00" disabled>
                </div>
            </div>
        </div>

        <!-- Left Side (InvoiceNo, InvoiceDate) -->
        <div class="col-md-2">
            <label class="form-label px-2">Invoice No.</label>
            <input type="text" class="form-control form-control-sm" id="invoiceNo" placeholder="Invoice No" readonly>

            <label class="form-label px-2 mt-2">Invoice Date</label>
            <input type="date" class="form-control form-control-sm" id="invoiceDate" value="<?php echo date('Y-m-d'); ?>">
        </div>
    </div>
</div>

            <!-- Data Grid -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered" id="productTable">
                    <thead>
                        <tr>
                            <th class="col-md-1">Product Code</th>
                            <th class="col-md-4">Product Name</th>
                            <th class="col-md-1">Qty</th>
                            <th class="col-md-1">Rate</th>
                            <th class="col-md-1">Discount %</th>
                            <th class="col-md-1">Amount</th>
                            <th class="col-md-1">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                               <input type="text" class="form-control search-box form-control-sm" data-type="product" placeholder="Search Product Code">
                               <ul class="dropdown-menu w-auto"></ul>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm productName">
                            </td>
                            <td><input type="number" class="form-control form-control-sm qty" value="0"></td>
                            <td><input type="number" class="form-control form-control-sm rate" value="0"></td>
                            <td><input type="number" class="form-control form-control-sm discount" value="0"></td>
                            <td><input type="text" class="form-control form-control-sm amount" readonly value="0.00"></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-link text-success p-0" onclick="addRow(this)">
                                    <i class="bi bi-plus-circle-fill fs-5"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-2" onclick="deleteRow(this)">
                                    <i class="bi bi-trash-fill fs-5"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Bottom Right Totals -->
            <div class="row justify-content-end">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-8 mb-2">
                        <label for="gudsName" class="form-label px-2">Guds Name</label>
                        <select class="form-select form-select-sm" id="gudsName">
                            <option value="" disabled selected>Select Guds</option>
                            <option>Zikria Guds</option>
                            <option>786 Guds</option>
                            <option>Chishti Guds</option>
                        </select>
                        </div>
                        <div class="col-md-4 mb-2">
                        <label for="corten" class="form-label  px-2">No. of Corten</label>
                        <input type="number" class="form-control form-control-sm corten" value="0" oninput="updateRowAndTotals()">
                        </div>
                        <div class="col-md-4 mb-2">
                        <label for="biltyExp" class="form-label px-2">Bilty Expanse</label>
                        <input type="number" class="form-control form-control-sm biltyExp" value="0" oninput="updateRowAndTotals()">
                        </div>
                        <div class="col-md-4 mb-2">
                        <label for="loaderExp" class="form-label px-2">Loder Expanse</label>
                        <input type="number" class="form-control form-control-sm loaderExp" value="0" oninput="updateRowAndTotals()">
                        </div>
                        <div class="col-md-4 mb-2">
                        <label for="biltyBalance" class="form-label px-2">Bilty Balance</label>
                        <input type="number" class="form-control form-control-sm biltyBalance" placeholder="0.00" disabled>
                        </div>
                        <div class="form-floating md-4 mb-2">
                        <textarea class="form-control" placeholder="Leave a comment here" id="floatingTextarea"></textarea>
                        <label for="floatingTextarea">Comments</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="col-md-4">
                        <label for="noOfItems" class="form-label">No. of Items</label>
                        <input type="number" id="noOfItems" class="form-control form-control-sm" value="1" readonly>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-2">
                        <label class="form-label">Invoice Total</label>
                        <input type="text" class="form-control" id="invoiceTotal" readonly value="0.00">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Invoice Discount</label>
                        <input type="number" class="form-control" id="invoiceDiscount" value="0" oninput="updateTotal()">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Grand Total</label>
                        <input type="text" class="form-control" id="grandTotal" readonly value="0.00">
                    </div>
                </div>
            </div>
            <!-- Save Button -->
            <div class="text-end">
                <button class="btn btn-primary mt-2">Save Invoice</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// ====== 1. On Page Load - Focus AreaCode ======
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("areaCode").focus();
});

// ====== Helper: Update No. of Items ======
function updateNoOfItems() {
    const tableBody = document.getElementById("productTable").getElementsByTagName('tbody')[0];
    const noOfItemsField = document.getElementById("noOfItems");
    if (noOfItemsField) {
        noOfItemsField.value = tableBody.rows.length;
    }
}

// ====== 2. Calculate Row Amount ======
function calculateRowAmount(row) {
    const qty = parseFloat(row.querySelector(".qty").value) || 0;
    const rate = parseFloat(row.querySelector(".rate").value) || 0;
    const discount = parseFloat(row.querySelector(".discount").value) || 0;
    
    const amount = (qty * rate) * (1 - discount / 100);
    row.querySelector(".amount").value = amount.toFixed(2);
    return amount;
}

// ====== 3. Update Invoice Total ======
function updateTotal() {
    let total = 0;
    const rows = document.querySelectorAll("#productTable tbody tr");
    
    rows.forEach(row => {
        total += calculateRowAmount(row);
    });
    
    document.getElementById("invoiceTotal").value = total.toFixed(2);
    updateInvoiceGrandTotal();
}

// ====== 4. Add Row ======
function addRow(btn) {
    const table = document.getElementById("productTable").getElementsByTagName('tbody')[0];
    const newRow = table.rows[0].cloneNode(true);

    // Clear all inputs in the new row
    newRow.querySelectorAll('input').forEach(input => {
        if (!input.classList.contains('amount')) {
            input.value = '0';
        } else {
            input.value = '0.00';
        }
    });
    
    // Reset selects
    newRow.querySelectorAll('select').forEach(select => {
        select.selectedIndex = 0;
    });

    table.appendChild(newRow);

    // Focus product code in new row
    newRow.querySelector(".productCode").focus();

    updateNoOfItems();
}

// ====== 5. Delete Row ======
function deleteRow(btn) {
    const row = btn.closest('tr');
    const tableBody = document.getElementById("productTable").getElementsByTagName('tbody')[0];

    if (tableBody.rows.length > 1) {
        row.remove();
        updateTotal();
        updateNoOfItems();
    } else {
        alert("At least one product row is required.");
    }
}

// ====== 6. Update Grand Total ======
function updateInvoiceGrandTotal() {
    let invoiceTotal = parseFloat(document.getElementById("invoiceTotal").value) || 0;
    let invoiceDiscount = parseFloat(document.getElementById("invoiceDiscount").value) || 0;
    
    let grandTotal = invoiceTotal *(1 - invoiceDiscount / 100);


    document.getElementById("grandTotal").value = grandTotal.toFixed(2);
}

// ====== 7. Update Bilty Balance ======
function calculateBiltyBalance() {
    let biltyExp = parseFloat(document.querySelector(".biltyExp")?.value) || 0;
    let loaderExp = parseFloat(document.querySelector(".loaderExp")?.value) || 0;
    let corten = parseFloat(document.querySelector(".corten")?.value) || 0;

    let total = corten * biltyExp;
    let biltyBalance = total - loaderExp;

    document.querySelector(".biltyBalance").value = biltyBalance.toFixed(2);
}

// ====== 8. Combined Update ======
function updateRowAndTotals() {
    calculateBiltyBalance();
    updateInvoiceGrandTotal();
}

// ====== Event Listeners ======
document.addEventListener("input", function(e) {
    if (e.target.classList.contains("qty") || 
        e.target.classList.contains("rate") || 
        e.target.classList.contains("discount")) {
        calculateRowAmount(e.target.closest('tr'));
        updateTotal();
    }
});

// Initialize with one row calculation
updateTotal();



const dataSets = {
  area: [
    { id: "A101", name: "North Zone North ZoneNorth Zone " },
    { id: "A102", name: "South Zone" },
    { id: "A103", name: "East Zone" },
  ],
  customer: [
    { id: "C001", name: "John Traders John TradersJohn Traders" },
    { id: "C002", name: "Alpha Distributors" },
    { id: "C003", name: "Beta Wholesalers" },
  ],
  product: [
    { id: "P101", name: "Apple iPhone 15 iPhone 15 iPhone 15 iPhone 15" },
    { id: "P102", name: "Samsung Galaxy S24" },
    { id: "P103", name: "Google Pixel 8" },
  ]
};

document.querySelectorAll('.search-box').forEach(input => {
  let dropdown = input.nextElementSibling;
  let currentFocus = -1;
  let visibleItems = [];

  // Show all results when clicked
  input.addEventListener('focus', function () {
    filterItems("", input.dataset.type);
  });

  // Filter on typing
  input.addEventListener('input', function () {
    filterItems(this.value, input.dataset.type);
  });

  // Keyboard navigation
  input.addEventListener('keydown', function (e) {
    if (!dropdown.classList.contains('show')) return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      currentFocus++;
      addActive();
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      currentFocus--;
      addActive();
    } else if (e.key === "Enter" || e.key === "Tab") {
      if (dropdown.classList.contains('show')) {
        e.preventDefault();
        if (currentFocus > -1 && visibleItems[currentFocus]) {
          selectItem(visibleItems[currentFocus]);
        } else if (visibleItems.length > 0) {
          selectItem(visibleItems[0]);
        }
      }
    }
  });

  function filterItems(query, type) {
    dropdown.innerHTML = '';
    visibleItems = [];
    currentFocus = -1;

    query = query.toLowerCase().trim();
    const list = dataSets[type];

    const idMatches = query ? list.filter(i => i.id.toLowerCase().includes(query)) : list;
    const nameMatches = query ? list.filter(i => i.name.toLowerCase().includes(query) && !idMatches.includes(i)) : [];

    if (idMatches.length) {
      dropdown.appendChild(makeHeader("IDs"));
      idMatches.forEach(item => addListItem(`${item.id} - ${item.name}`));
    }
    if (nameMatches.length) {
      dropdown.appendChild(makeHeader("Names"));
      nameMatches.forEach(item => addListItem(`${item.name} (ID: ${item.id})`));
    }

    if (visibleItems.length) {
      dropdown.classList.add('show');
      currentFocus = 0;
      addActive();
    } else {
      dropdown.classList.remove('show');
    }
  }

  function makeHeader(text) {
    const header = document.createElement('li');
    header.classList.add('dropdown-header');
    header.textContent = text;
    return header;
  }

  function addListItem(text) {
    const li = document.createElement('li');
    li.classList.add('dropdown-item');
    li.textContent = text;
    li.onclick = () => selectItem(li);
    dropdown.appendChild(li);
    visibleItems.push(li);
  }

  function selectItem(li) {
    input.value = li.textContent;
    dropdown.classList.remove('show');
  }

  function addActive() {
    if (!visibleItems.length) return;
    removeActive();
    if (currentFocus >= visibleItems.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = visibleItems.length - 1;
    visibleItems[currentFocus].classList.add('active');
    visibleItems[currentFocus].scrollIntoView({ block: "nearest" });
  }

  function removeActive() {
    visibleItems.forEach(i => i.classList.remove('active'));
  }

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.search-box')) {
      dropdown.classList.remove('show');
    }
  });
});

</script>
@endsection
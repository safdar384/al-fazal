@extends('layout.app')

@section('title', 'Edit Order')

@section('content')
<style>
    #customerSearch, .productSearch {
        border: 2px solid #e0e0e0;
        border-radius: 0.5rem;
        background-color: #f8f9fa;
        font-size: 0.875rem;
    }
    #customerSearch:focus, .productSearch:focus {
        border-color: #667eea;
        background-color: #fff;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.12);
        outline: 0;
    }
    #orderItemsTableWrap.table-responsive {
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch;
    }
    #itemsTable tbody td {
        position: relative;
        overflow: visible !important;
        vertical-align: top;
    }
    #itemsTable tbody tr.product-dropdown-open {
        position: relative;
        z-index: 200;
    }
    #customerDropdown, .productDropdown {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 10650;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #fff;
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2);
        margin-top: 4px;
        max-height: 320px;
        overflow-y: auto;
        width: 100%;
        min-width: 260px;
        display: none;
        pointer-events: auto;
    }
    .dropdown-item-custom {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
        font-size: 0.85rem;
    }
    .dropdown-item-custom:last-child { border-bottom: none; }
    .dropdown-item-custom:hover, .dropdown-item-custom.active {
        background: linear-gradient(135deg, #667eea 0%, #5568d3 100%);
        color: #fff;
    }
    .dropdown-item-custom.d-flex {
        align-items: center;
        gap: 0.5rem;
    }
    .dropdown-item-custom:hover .text-muted,
    .dropdown-item-custom.active .text-muted {
        color: rgba(255,255,255,0.9) !important;
    }
    .product-search-thumb {
        width: 36px;
        height: 36px;
        object-fit: cover;
        flex-shrink: 0;
    }
    .product-search-thumb-empty {
        width: 36px;
        height: 36px;
        font-size: 0.85rem;
    }
    #itemsTable thead th {
        background: #dbe9ff;
        font-weight: 600;
    }
    #itemsTable .btn-group .btn { padding: 0.2rem 0.45rem; }
    .position-relative { position: relative !important; }
    #editOrderCard.card .card-body { overflow: visible !important; }
</style>
<main class="app-main erp-invoice-page" id="main" tabindex="-1">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6"><h3 class="mb-0">Edit Order - {{ $order->orderNo }}</h3></div>
                <div class="col-sm-6 text-end text-muted small">Customer demand only (no stock/account impact)</div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card" id="editOrderCard">
                <div class="card-body">
                    <form id="orderForm">
                        @csrf
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Order No</label>
                                <input type="text" name="orderNo" class="form-control form-control-sm" value="{{ $order->orderNo }}" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Order Date</label>
                                <input type="date" name="orderDate" id="orderDate" class="form-control form-control-sm" value="{{ $order->orderDate ? $order->orderDate->format('Y-m-d') : '' }}" required>
                            </div>
                            <div class="col-md-3 position-relative">
                                <label class="form-label">Customer</label>
                                <input type="text" id="customerSearch" class="form-control form-control-sm" placeholder="Search customer..." autocomplete="off" required value="{{ trim(($order->customerName ?: '').($order->city ? ' — '.$order->city : '')) }}">
                                <input type="hidden" name="customerCode" id="customerCode" value="{{ $order->customerCode }}">
                                <input type="hidden" name="customerName" id="customerName" value="{{ $order->customerName }}">
                                <div id="customerDropdown"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" id="city" class="form-control form-control-sm" readonly value="{{ $order->city }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Guds Name</label>
                                <select name="gudsName" id="gudsName" class="form-select form-select-sm">
                                    <option value="">Select Guds</option>
                                    @foreach($guds as $gud)
                                        <option value="{{ $gud->gudsName }}" {{ $order->gudsName === $gud->gudsName ? 'selected' : '' }}>{{ $gud->gudsName }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Comments</label>
                                <input type="text" name="comments" id="comments" class="form-control form-control-sm" placeholder="Optional comments" value="{{ $order->comments }}">
                            </div>
                        </div>

                        <div class="erp-invoice-product-wrap table-responsive mb-2 erp-table-compact" id="orderItemsTableWrap">
                            <table class="table table-hover table-sm table-bordered align-middle mb-0" id="itemsTable">
                                <thead>
                                <tr>
                                    <th style="width: 32%;">Product</th>
                                    <th style="width: 12%;">Qty</th>
                                    <th style="width: 14%;">Rate</th>
                                    <th style="width: 14%;">Discount %</th>
                                    <th style="width: 18%;">Amount</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-md-4">
                                <input type="hidden" name="noOfItems" id="noOfItems" value="0">
                                <div class="d-flex justify-content-between small"><span>Order Total</span><strong id="orderTotalTxt">0.00</strong></div>
                                <div class="d-flex justify-content-between small"><span>Discount Total</span><strong id="discountTotalTxt">0.00</strong></div>
                                <div class="d-flex justify-content-between"><span>Grand Total</span><strong id="grandTotalTxt">0.00</strong></div>
                                <input type="hidden" name="orderTotal" id="orderTotal" value="{{ (float)$order->orderTotal }}">
                                <input type="hidden" name="orderDiscount" id="orderDiscount" value="{{ (float)$order->orderDiscount }}">
                                <input type="hidden" name="grandTotal" id="grandTotal" value="{{ (float)$order->grandTotal }}">
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-sm" id="btnSaveOrder">Update Order</button>
                            <a href="{{ route('order.preview', $order->orderNo) }}" target="_blank" class="btn btn-primary btn-sm">Preview</a>
                            <a href="{{ route('order.summary') }}" class="btn btn-secondary btn-sm">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const products = {!! $products->map(function ($p) {
    $stock = (float) ($p->stockQty ?? 0);
    if ($p->relationLoaded('variants') && $p->variants->count() > 0) {
        $stock = (float) $p->variants->sum('stockQty');
    }

    return [
        'code' => $p->productCode,
        'name' => $p->productName,
        'company' => $p->companyName ?? '',
        'rate' => (float) ($p->saleRate ?? 0),
        'stockQty' => $stock,
        'image' => $p->image_url,
        'hasVariants' => $p->relationLoaded('variants') && $p->variants->count() > 0,
    ];
})->values()->toJson() !!};
const customers = {!! $customers->map(function ($c) {
    $biz = trim((string) ($c->bussnessName ?: $c->custName));
    $city = trim((string) ($c->custCity ?? ''));
    return [
        'code' => $c->custCode,
        'name' => $c->custName,
        'business' => $c->bussnessName ?? '',
        'city' => $city,
        'label' => $city !== '' ? $biz.' — '.$city : $biz,
    ];
})->values()->toJson() !!};
const existingItems = {!! $order->items->map(function ($i) {
    return [
        'productCode' => $i->productCode,
        'productName' => $i->productName,
        'qty' => (float) $i->qty,
        'rate' => (float) $i->rate,
        'discount' => (float) ($i->discount ?? 0),
        'amount' => (float) $i->amount,
    ];
})->values()->toJson() !!};

const tbody = document.querySelector('#itemsTable tbody');
const customerSearch = document.getElementById('customerSearch');
const customerDropdown = document.getElementById('customerDropdown');

function focusAndSelect(el) {
    if (!el || el.disabled) return false;
    try {
        el.focus();
        if (el.tagName === 'INPUT' && el.type !== 'date' && el.type !== 'hidden' && typeof el.select === 'function') el.select();
    } catch (_) {}
    return true;
}
function getHeaderFocusChain() {
    return [document.getElementById('orderDate'), document.getElementById('customerSearch'), document.getElementById('gudsName'), document.getElementById('comments')].filter(Boolean);
}
function focusFirstProductSearch() {
    const ps = tbody.querySelector('tr .productSearch');
    return ps ? focusAndSelect(ps) : false;
}
function focusNextFieldFrom(fromEl) {
    const header = getHeaderFocusChain();
    const hIdx = header.indexOf(fromEl);
    if (hIdx !== -1) {
        if (hIdx < header.length - 1) focusAndSelect(header[hIdx + 1]); else focusFirstProductSearch();
        return;
    }
    const tr = fromEl.closest ? fromEl.closest('tr') : null;
    if (!tr || !tbody.contains(tr)) return;
    if (fromEl.classList.contains('productSearch')) {
        const typed = String(fromEl.value || '').trim();
        if (!tr.querySelector('.productCode').value) {
            if (typed) {
                showWarning('Product not found', 'No product matches your search. Clear the field or try another name/code.');
                return focusAndSelect(fromEl);
            }
            const guds = document.getElementById('gudsName');
            return guds ? focusAndSelect(guds) : false;
        }
        return focusAndSelect(tr.querySelector('.qty'));
    }
    if (fromEl.classList.contains('qty')) {
        if (!tr.querySelector('.productCode').value) return focusAndSelect(tr.querySelector('.productSearch'));
        return focusAndSelect(tr.querySelector('.rate'));
    }
    if (fromEl.classList.contains('rate')) return focusAndSelect(tr.querySelector('.discount'));
    if (fromEl.classList.contains('discount')) {
        let nextTr = tr.nextElementSibling;
        if (!nextTr) {
            addRow();
            nextTr = tbody.querySelector('tr:last-child');
        }
        const nextPs = nextTr && nextTr.querySelector('.productSearch');
        if (nextPs) focusAndSelect(nextPs);
    }
}
function bindEnterToNext(el) {
    if (!el) return;
    el.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        focusNextFieldFrom(el);
    });
}

function addRow(item = null) {
    const tr = document.createElement('tr');
    const pCode = item?.productCode || '';
    const pName = item?.productName || '';
    const qty = item?.qty ?? 1;
    const rate = item?.rate ?? 0;
    const discount = item?.discount ?? 0;
    const amount = item?.amount ?? 0;
    tr.innerHTML = `
        <td>
            <div class="position-relative">
                <input type="text" class="form-control form-control-sm productSearch" placeholder="Type to search product..." autocomplete="off" required value="${pName && pCode ? `${pName} (${pCode})` : ''}">
                <input type="hidden" class="productCode" value="${pCode}">
                <input type="hidden" class="productName" value="${pName}">
                <div class="productDropdown"></div>
            </div>
        </td>
        <td><input type="number" class="form-control form-control-sm text-center qty" min="1" value="${qty}" required placeholder="Qty"></td>
        <td><input type="number" class="form-control form-control-sm text-end rate" min="0" step="0.01" value="${rate || ''}" required placeholder="Rate"></td>
        <td><input type="number" class="form-control form-control-sm text-end discount" min="0" max="100" step="0.01" value="${discount || ''}" placeholder="Disc %"></td>
        <td><input type="number" class="form-control form-control-sm text-end amount" value="${amount || ''}" readonly placeholder="Amount"></td>
        <td class="text-center">
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-success addRowInline" title="Add Row">+</button>
                <button type="button" class="btn btn-danger removeRow" title="Delete Row">X</button>
            </div>
        </td>`;
    tbody.appendChild(tr);
    bindRow(tr);
    recalc();
}

function bindRow(tr) {
    const productCode = tr.querySelector('.productCode');
    const productName = tr.querySelector('.productName');
    const productSearch = tr.querySelector('.productSearch');
    const productDropdown = tr.querySelector('.productDropdown');
    const qty = tr.querySelector('.qty');
    const rate = tr.querySelector('.rate');
    const discount = tr.querySelector('.discount');
    const amount = tr.querySelector('.amount');

    function rowCalc() {
        const q = parseFloat(qty.value || 0);
        const r = parseFloat(rate.value || 0);
        const d = parseFloat(discount.value || 0);
        const gross = q * r;
        amount.value = (gross - (gross * d / 100)).toFixed(2);
        recalc();
    }
    function setProductDropdownOpen(open) { tr.classList.toggle('product-dropdown-open', !!open); }
    function renderProducts(term = '') {
        const q = term.toLowerCase().trim();
        if (!q) {
            productDropdown.innerHTML = '';
            productDropdown.style.display = 'none';
            setProductDropdownOpen(false);
            if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown(productDropdown);
            return;
        }
        const filtered = products.filter(p =>
            (p.name || '').toLowerCase().includes(q) ||
            (p.code || '').toLowerCase().includes(q) ||
            (p.company || '').toLowerCase().includes(q)
        ).slice(0, 10);
        if (filtered.length === 0) {
            productDropdown.innerHTML = '<div class="dropdown-item-custom text-muted">No products found</div>';
            productDropdown.style.display = 'block';
            setProductDropdownOpen(true);
            if (typeof erpPositionFloatingDropdown === 'function') {
                erpPositionFloatingDropdown(productSearch, productDropdown);
            }
            return;
        }
        productDropdown.innerHTML = filtered.map((p, idx) => {
            const stock = parseFloat(p.stockQty) || 0;
            const stockClass = stock <= 0 ? 'bg-danger' : (stock <= 5 ? 'bg-warning text-dark' : 'bg-success');
            const thumb = p.image
                ? `<img src="${p.image}" alt="" class="product-search-thumb rounded border">`
                : `<span class="product-search-thumb product-search-thumb-empty rounded border d-inline-flex align-items-center justify-content-center bg-light text-muted"><i class="bi bi-image"></i></span>`;
            return `<div class="dropdown-item-custom d-flex ${idx === 0 ? 'active' : ''}" data-code="${p.code}" data-name="${p.name}" data-rate="${p.rate}">
                ${thumb}
                <div class="flex-grow-1 min-w-0">
                    <strong class="d-block text-truncate">${p.name}</strong>
                    <div class="text-muted small text-truncate">${p.code}${p.company ? ' · ' + p.company : ''}</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                    <span class="badge ${stockClass}">Stock: ${Number.isInteger(stock) ? stock : stock.toFixed(2)}</span>
                    <span class="badge bg-info">Rs.${parseFloat(p.rate).toFixed(2)}</span>
                </div>
            </div>`;
        }).join('');
        productDropdown.style.display = 'block';
        setProductDropdownOpen(true);
        if (typeof erpPositionFloatingDropdown === 'function') {
            erpPositionFloatingDropdown(productSearch, productDropdown);
        }
    }
    function selectProduct(code, name, r) {
        const pMeta = products.find(function (x) { return String(x.code) === String(code); });
        const hasVariants = !!(pMeta && pMeta.hasVariants);
        if (!hasVariants) {
            const duplicate = [...tbody.querySelectorAll('tr')].some(function (other) {
                return other !== tr && other.querySelector('.productCode').value === code;
            });
            if (duplicate) {
                productSearch.value = productCode.value ? productSearch.value : '';
                productDropdown.style.display = 'none';
                setProductDropdownOpen(false);
                if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown(productDropdown);
                showWarning('Duplicate product', 'This product is already added.');
                productSearch.focus();
                return;
            }
        }
        productCode.value = code;
        productName.value = name;
        productSearch.value = `${name} (${code})`;
        rate.value = parseFloat(r || 0).toFixed(2);
        productDropdown.style.display = 'none';
        setProductDropdownOpen(false);
        if (typeof erpResetFloatingDropdown === 'function') {
            erpResetFloatingDropdown(productDropdown);
        }
        rowCalc();
        setTimeout(() => { qty.focus(); qty.select(); }, 50);
    }

    productSearch.addEventListener('focus', () => renderProducts(productSearch.value));
    productSearch.addEventListener('input', () => {
        productCode.value = '';
        productName.value = '';
        renderProducts(productSearch.value);
    });
    productDropdown.addEventListener('mousedown', (e) => e.preventDefault());
    productDropdown.addEventListener('click', (e) => {
        const item = e.target.closest('.dropdown-item-custom');
        if (!item) return;
        e.stopPropagation();
        selectProduct(item.dataset.code, item.dataset.name, item.dataset.rate);
    });
    productSearch.addEventListener('keydown', (e) => {
        const items = [...productDropdown.querySelectorAll('.dropdown-item-custom')];
        const activeIndex = items.findIndex(i => i.classList.contains('active'));
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!items.length) return;
            const next = activeIndex < 0 ? 0 : Math.min(activeIndex + 1, items.length - 1);
            items.forEach(i => i.classList.remove('active'));
            items[next].classList.add('active');
            if (productDropdown.style.display === 'block' && typeof erpPositionFloatingDropdown === 'function') {
                erpPositionFloatingDropdown(productSearch, productDropdown);
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!items.length) return;
            const prev = activeIndex <= 0 ? 0 : activeIndex - 1;
            items.forEach(i => i.classList.remove('active'));
            items[prev].classList.add('active');
            if (productDropdown.style.display === 'block' && typeof erpPositionFloatingDropdown === 'function') {
                erpPositionFloatingDropdown(productSearch, productDropdown);
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const selected = items.find(i => i.classList.contains('active') && i.dataset.code);
            const typed = String(productSearch.value || '').trim();
            if (selected && selected.dataset.code) {
                selectProduct(selected.dataset.code, selected.dataset.name, selected.dataset.rate);
            } else if (productCode.value) {
                focusNextFieldFrom(productSearch);
            } else if (!typed) {
                productDropdown.style.display = 'none';
                setProductDropdownOpen(false);
                if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown(productDropdown);
                focusNextFieldFrom(productSearch);
            } else {
                showWarning('Product not found', 'No product matches your search. Clear the field or try another name/code.');
                productDropdown.style.display = 'none';
                setProductDropdownOpen(false);
                if (typeof erpResetFloatingDropdown === 'function') erpResetFloatingDropdown(productDropdown);
                productSearch.focus();
            }
        } else if (e.key === 'Escape') {
            productDropdown.style.display = 'none';
            setProductDropdownOpen(false);
            if (typeof erpResetFloatingDropdown === 'function') {
                erpResetFloatingDropdown(productDropdown);
            }
        }
    });

    qty.addEventListener('input', rowCalc);
    rate.addEventListener('input', rowCalc);
    discount.addEventListener('input', rowCalc);
    bindEnterToNext(qty);
    bindEnterToNext(rate);
    bindEnterToNext(discount);
    tr.querySelector('.addRowInline').addEventListener('click', addRow);
    tr.querySelector('.removeRow').addEventListener('click', () => {
        if (tbody.querySelectorAll('tr').length === 1) return;
        setProductDropdownOpen(false);
        tr.remove();
        recalc();
    });
}

function recalc() {
    const rows = [...tbody.querySelectorAll('tr')];
    let orderTotal = 0, grandTotal = 0;
    rows.forEach(tr => {
        const q = parseFloat(tr.querySelector('.qty').value || 0);
        const r = parseFloat(tr.querySelector('.rate').value || 0);
        const a = parseFloat(tr.querySelector('.amount').value || 0);
        orderTotal += q * r;
        grandTotal += a;
    });
    const discountTotal = orderTotal - grandTotal;
    document.getElementById('orderTotalTxt').textContent = orderTotal.toFixed(2);
    document.getElementById('discountTotalTxt').textContent = discountTotal.toFixed(2);
    document.getElementById('grandTotalTxt').textContent = grandTotal.toFixed(2);
    document.getElementById('orderTotal').value = orderTotal.toFixed(2);
    document.getElementById('orderDiscount').value = discountTotal.toFixed(2);
    document.getElementById('grandTotal').value = grandTotal.toFixed(2);
    document.getElementById('noOfItems').value = rows.length;
}

function customerLabel(c) {
    const biz = (c.business || c.name || '').trim();
    const city = (c.city || '').trim();
    return city ? `${biz} — ${city}` : biz;
}
function renderCustomers(term = '') {
    const q = term.toLowerCase().trim();
    const filtered = customers.filter(c =>
        (c.name || '').toLowerCase().includes(q) ||
        (c.business || '').toLowerCase().includes(q) ||
        (c.city || '').toLowerCase().includes(q) ||
        (c.code || '').toLowerCase().includes(q) ||
        (c.label || '').toLowerCase().includes(q)
    ).slice(0, 40);
    customerDropdown.innerHTML = filtered.map((c, idx) => {
        const title = (c.business || c.name || '').trim();
        const sub = [c.city, c.business && c.name ? c.name : ''].filter(Boolean).join(' · ');
        return `<div class="dropdown-item-custom ${idx === 0 ? 'active' : ''}" data-code="${c.code}" data-name="${(c.business || c.name || '').replace(/"/g, '&quot;')}" data-city="${c.city}"><strong>${title}</strong><div class="text-muted" style="font-size:0.75rem;">${sub || c.code}</div></div>`;
    }).join('');
    customerDropdown.style.display = filtered.length ? 'block' : 'none';
}
function selectCustomer(code, name, city) {
    const cust = customers.find(c => c.code == code);
    const storeName = cust ? ((cust.business || cust.name || '').trim()) : name;
    document.getElementById('customerCode').value = code;
    document.getElementById('customerName').value = storeName;
    customerSearch.value = cust ? customerLabel(cust) : `${name}${city ? ' — ' + city : ''}`;
    document.getElementById('city').value = city || '';
    customerDropdown.style.display = 'none';
}

customerSearch.addEventListener('focus', () => renderCustomers(customerSearch.value));
customerSearch.addEventListener('input', () => {
    document.getElementById('customerCode').value = '';
    document.getElementById('customerName').value = '';
    renderCustomers(customerSearch.value);
});
customerDropdown.addEventListener('mousedown', (e) => e.preventDefault());
customerDropdown.addEventListener('click', (e) => {
    const item = e.target.closest('.dropdown-item-custom');
    if (!item) return;
    selectCustomer(item.dataset.code, item.dataset.name, item.dataset.city);
});
customerSearch.addEventListener('keydown', (e) => {
    const items = [...customerDropdown.querySelectorAll('.dropdown-item-custom')];
    const activeIndex = items.findIndex(i => i.classList.contains('active'));
    const listOpen = customerDropdown.style.display !== 'none' && items.length > 0;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!items.length) return;
        const next = activeIndex < 0 ? 0 : Math.min(activeIndex + 1, items.length - 1);
        items.forEach(i => i.classList.remove('active'));
        items[next].classList.add('active');
        return;
    }
    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!items.length) return;
        const prev = activeIndex <= 0 ? 0 : activeIndex - 1;
        items.forEach(i => i.classList.remove('active'));
        items[prev].classList.add('active');
        return;
    }
    if (e.key === 'Enter') {
        e.preventDefault();
        if (listOpen) {
            const pick = items.find(i => i.classList.contains('active')) || items[0];
            selectCustomer(pick.dataset.code, pick.dataset.name, pick.dataset.city);
            focusAndSelect(document.getElementById('gudsName'));
            return;
        }
        if (document.getElementById('customerCode').value) focusNextFieldFrom(customerSearch);
    }
    if (e.key === 'Escape') customerDropdown.style.display = 'none';
});
bindEnterToNext(document.getElementById('orderDate'));
bindEnterToNext(document.getElementById('gudsName'));
bindEnterToNext(document.getElementById('comments'));

document.addEventListener('click', (e) => {
    if (!e.target.closest('#customerSearch') && !e.target.closest('#customerDropdown')) customerDropdown.style.display = 'none';
    if (!e.target.closest('.productSearch') && !e.target.closest('.productDropdown')) {
        document.querySelectorAll('.productDropdown').forEach(d => {
            d.style.display = 'none';
            if (typeof erpResetFloatingDropdown === 'function') {
                erpResetFloatingDropdown(d);
            }
        });
        document.querySelectorAll('#itemsTable tbody tr.product-dropdown-open').forEach(r => r.classList.remove('product-dropdown-open'));
    }
});

window.addEventListener('resize', () => {
    document.querySelectorAll('#itemsTable tbody .productDropdown').forEach(d => {
        if (d.style.display !== 'block') return;
        const tr = d.closest('tr');
        const inp = tr && tr.querySelector('.productSearch');
        if (inp && typeof erpPositionFloatingDropdown === 'function') {
            erpPositionFloatingDropdown(inp, d);
        }
    });
});

document.getElementById('orderForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const customerCode = document.getElementById('customerCode').value;
    const customerName = document.getElementById('customerName').value;
    if (!customerCode || !customerName) return alert('Select customer');

    const items = [...tbody.querySelectorAll('tr')].map(tr => {
        const code = tr.querySelector('.productCode').value;
        const name = tr.querySelector('.productName').value;
        return {
            productCode: code,
            productName: name || '',
            qty: parseFloat(tr.querySelector('.qty').value || 0),
            rate: parseFloat(tr.querySelector('.rate').value || 0),
            discount: parseFloat(tr.querySelector('.discount').value || 0),
            amount: parseFloat(tr.querySelector('.amount').value || 0),
        };
    }).filter(i => i.productCode);
    if (items.length === 0) return alert('Add at least one item.');

    const payload = {
        _token: document.querySelector('input[name="_token"]').value,
        orderDate: document.querySelector('input[name="orderDate"]').value,
        customerCode,
        customerName,
        city: document.getElementById('city').value,
        gudsName: document.querySelector('select[name="gudsName"]').value,
        comments: document.querySelector('input[name="comments"]').value,
        noOfItems: parseInt(document.getElementById('noOfItems').value || '0', 10),
        orderTotal: parseFloat(document.getElementById('orderTotal').value || 0),
        orderDiscount: parseFloat(document.getElementById('orderDiscount').value || 0),
        grandTotal: parseFloat(document.getElementById('grandTotal').value || 0),
        items
    };

    const res = await fetch(`{{ route('order.update', $order->orderNo) }}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': payload._token
        },
        body: JSON.stringify(payload)
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) return alert(data.message || 'Failed to update order');
    window.location.href = `{{ route('order.preview', $order->orderNo) }}`;
});

if (existingItems.length > 0) {
    existingItems.forEach(item => addRow(item));
} else {
    addRow();
}
recalc();
</script>
@endsection


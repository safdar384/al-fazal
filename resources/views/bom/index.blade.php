@extends('layout.app')
@section('title', 'Product Cost')

@push('styles')
<style>
  .pc-card {
    box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
    border-radius: 4px;
    overflow: visible;
    border: 1px solid #d5dde8;
  }
  .pc-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    border: none;
    border-radius: 4px 4px 0 0;
    padding: 0.75rem 1rem;
  }
  .pc-label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #0f2747;
    margin-bottom: 0.25rem;
  }
  .bom-product-search-wrap { position: relative; max-width: 560px; }
  #bomProductDropdown.dropdown-menu {
    display: none;
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    z-index: 1060;
    max-height: 280px;
    overflow-y: auto;
    margin-top: 4px;
    box-shadow: 0 6px 18px rgba(15, 39, 71, 0.18);
    border: 1px solid #d5dde8;
    border-radius: 4px;
    padding: 0.25rem 0;
  }
  #bomProductDropdown .dropdown-item {
    padding: 0.55rem 0.85rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f3f5;
    white-space: normal;
  }
  #bomProductDropdown .dropdown-item:last-child { border-bottom: none; }
  #bomProductDropdown .dropdown-item:hover,
  #bomProductDropdown .dropdown-item.active {
    background: #0f2747;
    color: #fff;
  }
  #bomProductDropdown .dropdown-item:hover .text-muted,
  #bomProductDropdown .dropdown-item.active .text-muted {
    color: rgba(255,255,255,0.85) !important;
  }
  .pc-table thead th {
    background: #0f2747;
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border-color: #0a1c33;
    white-space: nowrap;
    vertical-align: middle;
    padding: 0.55rem 0.75rem;
  }
  .pc-table tbody td {
    font-size: 0.85rem;
    border-color: #e8eef5;
    vertical-align: middle;
    padding: 0.65rem 0.75rem;
  }
  .pc-table tbody tr:nth-child(even) td { background: #f4f7fb; }
  .pc-table tbody tr:hover td { background: #e8eef5; }
  .pc-code {
    display: inline-block;
    padding: 0.1rem 0.4rem;
    border-radius: 3px;
    background: #e8eef5;
    color: #0f2747;
    font-weight: 700;
    font-size: 0.72rem;
  }
  .pc-badge {
    background: rgba(255,255,255,0.2);
    color: #fff;
    font-weight: 600;
    font-size: 0.75rem;
  }
  .pc-empty {
    background: #f4f7fb;
  }
  .form-control:focus {
    border-color: #1a4a8a;
    box-shadow: 0 0 0 0.2rem rgba(15, 39, 71, 0.12);
  }
</style>
@endpush

@section('content')
<main class="app-main" id="main" tabindex="-1">
  <div class="app-content-header">
    <div class="container-fluid">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0 fw-bold" style="color:#0f2747;">
            <i class="bi bi-diagram-3 me-2"></i>Product Cost
          </h3>
          <p class="text-muted small mb-0">Stock Management · BOM &amp; unit cost</p>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Stock Management</span></li>
            <li class="breadcrumb-item active">Product Cost</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid">
      <div class="card pc-card border-0 mb-3">
        <div class="card-header pc-card-head">
          <h6 class="mb-0 text-white fw-semibold">
            <i class="bi bi-search me-2"></i>Select Product
          </h6>
        </div>
        <div class="card-body p-3">
          <label class="pc-label">Finished product <span class="text-muted fw-normal">(type name or code to search)</span></label>
          <div class="bom-product-search-wrap">
            <input type="text" class="form-control" id="bomProductSearch"
                   placeholder="Type product name or code..." autocomplete="off">
            <select class="form-select form-select-sm d-none" id="bomProductSelect">
              <option value="">Select Product</option>
              @foreach($products as $p)
                <option value="{{ $p->productCode }}"
                        data-name="{{ $p->productName }}"
                        data-company="{{ $p->companyName ?? '' }}">
                  {{ $p->productName }} — {{ $p->companyName ?: $p->productCode }}
                </option>
              @endforeach
            </select>
            <div id="bomProductDropdown" class="dropdown-menu w-100"></div>
          </div>
        </div>
      </div>

      <div class="card pc-card border-0">
        <div class="card-header pc-card-head d-flex justify-content-between align-items-center">
          <h6 class="mb-0 text-white fw-semibold">
            <i class="bi bi-table me-2"></i>Product Cost List
          </h6>
          <span class="badge pc-badge" id="bomRowCount">0</span>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 pc-table" id="bomProductsTable">
              <thead>
                <tr>
                  <th>Product</th>
                  <th class="text-end" style="width: 160px">Unit Cost</th>
                  <th class="text-center" style="width: 220px">Action</th>
                </tr>
              </thead>
              <tbody id="bomProductsBody">
                <tr id="bomEmptyRow" class="pc-empty">
                  <td colspan="3" class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                    Search and select a finished product above to show it here.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
$(function () {
  const BOM_PRODUCTS = @json($productsForJs);
  const BOM_BUILDER_BASE = @json(url('/bom'));
  const BOM_REPORT_BASE = @json(url('/bom'));

  const productByCode = {};
  BOM_PRODUCTS.forEach(function (p) { productByCode[p.code] = p; });

  const $search = $('#bomProductSearch');
  const $select = $('#bomProductSelect');
  const $dropdown = $('#bomProductDropdown');
  const $tbody = $('#bomProductsBody');
  const addedCodes = new Set();

  function fmt(n, dec) {
    dec = dec === undefined ? 2 : dec;
    return (parseFloat(n) || 0).toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec });
  }

  function filterProducts(term) {
    term = (term || '').toLowerCase().trim();
    if (!term) return [];
    return BOM_PRODUCTS.filter(function (p) {
      return p.name.toLowerCase().includes(term)
        || p.code.toLowerCase().includes(term)
        || (p.company && p.company.toLowerCase().includes(term));
    }).slice(0, 15);
  }

  function renderDropdown(term) {
    const list = filterProducts(term);
    if (!term) {
      $dropdown.hide().empty();
      return;
    }
    if (!list.length) {
      $dropdown.html('<div class="dropdown-item text-muted">No products found</div>').show();
      return;
    }
    let html = '';
    list.forEach(function (p, idx) {
      const costHint = p.hasCost ? 'Unit cost: ' + fmt(p.unitCost, 2) + ' ' + p.currency : 'No cost yet';
      html += '<a class="dropdown-item ' + (idx === 0 ? 'active' : '') + '" href="javascript:void(0);" data-code="' + p.code + '">'
        + '<strong>' + p.name + '</strong>'
        + '<div class="text-muted small">' + p.code + (p.company ? ' · ' + p.company : '') + ' · ' + costHint + '</div>'
        + '</a>';
    });
    $dropdown.html(html).show();
  }

  function updateRowCount() {
    const n = $tbody.find('tr[data-product-code]').length;
    $('#bomRowCount').text(n);
    $('#bomEmptyRow').toggle(n === 0);
  }

  function buildActionCell(p) {
    const editUrl = BOM_BUILDER_BASE + '/' + encodeURIComponent(p.code);
    if (p.hasCost) {
      const viewUrl = BOM_REPORT_BASE + '/' + encodeURIComponent(p.code) + '/report';
      return '<div class="d-flex flex-wrap justify-content-center gap-1">'
        + '<a class="btn btn-sm btn-outline-secondary" href="' + viewUrl + '"><i class="bi bi-eye me-1"></i>View</a>'
        + '<a class="btn btn-sm btn-outline-secondary" href="' + editUrl + '"><i class="bi bi-pencil-square me-1"></i>Edit</a>'
        + '</div>';
    }
    return '<a class="btn btn-sm text-white" style="background:#0f2747;border-color:#0f2747;" href="' + editUrl + '">'
      + '<i class="bi bi-calculator me-1"></i>Make Cost</a>';
  }

  function addProductRow(code, scrollTo) {
    const p = productByCode[code];
    if (!p || addedCodes.has(code)) {
      if (p && addedCodes.has(code)) {
        const $existing = $tbody.find('tr[data-product-code="' + code + '"]');
        if (scrollTo && $existing.length) {
          $existing[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          $existing.addClass('table-warning');
          setTimeout(function () { $existing.removeClass('table-warning'); }, 1200);
        }
      }
      return;
    }
    addedCodes.add(code);

    const costCell = p.hasCost
      ? '<span class="fw-semibold text-success">' + fmt(p.unitCost, 2) + '</span> <small class="text-muted">' + p.currency + '</small>'
        + (p.effectiveFrom ? '<br><small class="text-muted">from ' + p.effectiveFrom + '</small>' : '')
      : '<span class="text-muted">—</span>';

    const row = '<tr data-product-code="' + p.code + '">'
      + '<td><div class="fw-semibold" style="color:#0f2747;">' + p.name + '</div>'
      + '<span class="pc-code">' + p.code + '</span>'
      + (p.company ? ' <small class="text-muted ms-1">' + p.company + '</small>' : '')
      + '</td>'
      + '<td class="text-end">' + costCell + '</td>'
      + '<td class="text-center">' + buildActionCell(p) + '</td>'
      + '</tr>';

    $('#bomEmptyRow').hide();
    $tbody.append(row);
    updateRowCount();

    if (scrollTo) {
      const $row = $tbody.find('tr[data-product-code="' + code + '"]');
      $row[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }

  function selectProduct(code) {
    const p = productByCode[code];
    if (!p) return;
    $select.val(code);
    $search.val(p.display);
    $dropdown.hide();
    addProductRow(code, true);
  }

  $search.on('input focus', function () {
    renderDropdown($search.val());
  });

  $search.on('keydown', function (e) {
    const $items = $dropdown.find('.dropdown-item[data-code]');
    const $active = $items.filter('.active');
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (!$items.length) return;
      let idx = $active.length ? Math.min($items.index($active) + 1, $items.length - 1) : 0;
      $items.removeClass('active').eq(idx).addClass('active');
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$active.length) return;
      let idx = Math.max($items.index($active) - 1, 0);
      $items.removeClass('active').eq(idx).addClass('active');
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const $sel = $items.filter('.active');
      if ($sel.length) selectProduct($sel.data('code'));
    } else if (e.key === 'Escape') {
      $dropdown.hide();
    }
  });

  $dropdown.on('click', '.dropdown-item[data-code]', function (e) {
    e.preventDefault();
    selectProduct($(this).data('code'));
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#bomProductSearch, #bomProductDropdown').length) {
      $dropdown.hide();
    }
  });

  setTimeout(function () { $search.focus(); }, 300);
});
</script>
@endpush

{{-- Purchase Invoice: Add Net Rate modal (stores via customerDiscount.store, rateType=Purchase) --}}
<style>
  .net-rate-link {
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
  }
  .net-rate-link:hover { text-decoration: underline; }
  #netRateModal .nr-product-search-wrap { position: relative; }
  #netRateModal .nrProductSearch.has-product { padding-right: 1.85rem; }
  #netRateModal .btn-clear-nr-product {
    position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
    z-index: 5; width: 22px; height: 22px; padding: 0; border: none; border-radius: 50%;
    background: transparent; color: #6b7c8d; display: none; align-items: center; justify-content: center;
    line-height: 1; font-size: 0.75rem;
  }
  #netRateModal .btn-clear-nr-product:hover { background: #fde8ea; color: #dc3545; }
  #netRateModal .nrProductDropdown {
    position: absolute; top: 100%; left: 0; z-index: 10650;
    border: 1px solid #dee2e6; border-radius: 0.375rem; background: #fff;
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.2); margin-top: 4px;
    max-height: 280px; overflow-y: auto; min-width: 100%; display: none;
  }
  #netRateModal .nrProductDropdown .dropdown-item {
    padding: 8px 12px; cursor: pointer; font-size: 0.85rem;
    border-bottom: 1px solid #f1f3f5;
  }
  #netRateModal .nrProductDropdown .dropdown-item:last-child { border-bottom: none; }
  #netRateModal .nrProductDropdown .dropdown-item:hover,
  #netRateModal .nrProductDropdown .dropdown-item.active {
    background: #0f2747; color: #fff;
  }
  #netRateModal .nrProductDropdown .dropdown-item:hover .text-muted,
  #netRateModal .nrProductDropdown .dropdown-item.active .text-muted {
    color: rgba(255,255,255,0.85) !important;
  }
  #netRateModal .modal-header {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
  }
</style>

<div class="modal fade" id="netRateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-percent me-2"></i>Add Net Rate</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="netRateForm">
          <div class="mb-2">
            <label class="form-label fw-semibold">Supplier</label>
            <input type="text" class="form-control form-control-sm bg-light" id="nrCustomerLabel" readonly>
            <input type="hidden" id="nrCustomerCode">
          </div>
          <div class="mb-2">
            <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
            <div class="nr-product-search-wrap">
              <input type="text"
                     class="form-control form-control-sm nrProductSearch"
                     id="nrProductSearch"
                     placeholder="Type to search product..."
                     autocomplete="off"
                     required>
              <button type="button" class="btn-clear-nr-product" id="btnClearNrProduct" title="Clear product">
                <i class="bi bi-x"></i>
              </button>
              <input type="hidden" id="nrProductCode" value="">
              <div class="nrProductDropdown" id="nrProductDropdown"></div>
            </div>
          </div>
          <div class="mb-2 d-none" id="nrVariantWrap">
            <label class="form-label fw-semibold">Variant</label>
            <select class="form-select form-select-sm" id="nrVariantSelect">
              <option value="">No Variant (base rate)</option>
            </select>
          </div>
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Rate Type</label>
              <input type="text" class="form-control form-control-sm bg-light" value="Purchase" readonly>
              <input type="hidden" id="nrRateType" value="Purchase">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Effective From <span class="text-danger">*</span></label>
              <input type="date" class="form-control form-control-sm" id="nrEffectiveFrom" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Net Rate <span class="text-danger">*</span></label>
              <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="nrSpecialRate" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Discount %</label>
              <input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm" id="nrDiscountPercent" value="0">
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold">Remarks</label>
              <input type="text" class="form-control form-control-sm" id="nrRemarks" maxlength="255" placeholder="Optional">
            </div>
          </div>
        </form>

        <hr class="my-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0"><i class="bi bi-list-check me-1"></i>Saved Purchase Net Rates</h6>
          <small class="text-muted" id="nrRatesHint">—</small>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-hover table-bordered align-middle mb-0" id="nrRatesTable">
            <thead class="table-secondary">
              <tr>
                <th style="width:5%;" class="text-center">#</th>
                <th>Product</th>
                <th style="width:18%;">Variant</th>
                <th class="text-end" style="width:14%;">Rate</th>
                <th style="width:14%;">Effective</th>
                <th class="text-center" style="width:12%;">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="6" class="text-center text-muted py-3">No rates saved</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="nrFormResetBtn">Reset</button>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        <button type="submit" form="netRateForm" class="btn btn-success btn-sm" id="nrSaveBtn">
          <i class="bi bi-save me-1"></i>Save Net Rate
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  let nrRatesCache = [];

  function nrFmt(n) {
    return (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function nrProductList() {
    if (Array.isArray(window.allProducts) && window.allProducts.length) {
      return window.allProducts;
    }
    return [];
  }

  function nrVariantsFor(code) {
    const map = window.productVariants || {};
    return map[code] || [];
  }

  function nrProductByCode(code) {
    return nrProductList().find(function (p) { return String(p.code) === String(code); }) || null;
  }

  function hideNrProductDropdown() {
    const $dd = $('#nrProductDropdown');
    $dd.hide();
    if (typeof erpResetFloatingDropdown === 'function' && $dd[0]) {
      erpResetFloatingDropdown($dd[0]);
    }
  }

  function syncNrProductDropdown() {
    const $dd = $('#nrProductDropdown');
    if ($dd.is(':visible') && typeof erpPositionFloatingDropdown === 'function') {
      erpPositionFloatingDropdown($('#nrProductSearch')[0], $dd[0]);
    }
  }

  function syncNrClearBtn() {
    const has = !!$('#nrProductCode').val();
    $('#btnClearNrProduct').css('display', has ? 'inline-flex' : 'none');
    $('#nrProductSearch').toggleClass('has-product', has);
  }

  function clearNrProductSelection() {
    $('#nrProductCode').val('');
    $('#nrProductSearch').val('');
    $('#nrVariantWrap').addClass('d-none');
    $('#nrVariantSelect').html('<option value="">No Variant (base rate)</option>');
    hideNrProductDropdown();
    syncNrClearBtn();
  }

  function fillNrVariants(code, selectedId) {
    const variants = nrVariantsFor(code);
    const $wrap = $('#nrVariantWrap');
    const $sel = $('#nrVariantSelect').empty().append('<option value="">No Variant (base rate)</option>');
    if (!variants.length) {
      $wrap.addClass('d-none');
      return;
    }
    variants.forEach(function (v) {
      const label = (v.name || '') + (v.value ? ': ' + v.value : '');
      $sel.append(
        $('<option>', {
          value: v.id,
          text: label,
          'data-name': v.name || '',
          'data-value': v.value || '',
          'data-add-cost': v.additionalCost || 0,
        })
      );
    });
    $wrap.removeClass('d-none');
    if (selectedId) $sel.val(String(selectedId));
  }

  function selectNrProduct(code) {
    const p = nrProductByCode(code);
    if (!p) return;
    const label = (p.name || p.display || code) + (p.code ? ' (' + p.code + ')' : '');
    $('#nrProductCode').val(p.code);
    $('#nrProductSearch').val(label);
    hideNrProductDropdown();
    syncNrClearBtn();
    fillNrVariants(p.code);
    const base = Number(p.cost != null ? p.cost : (p.rate || 0));
    $('#nrSpecialRate').val(base.toFixed(2));
  }

  function filterNrProducts(term) {
    const q = String(term || '').toLowerCase().trim();
    const $dd = $('#nrProductDropdown').empty();
    if (!q) {
      hideNrProductDropdown();
      return;
    }
    const filtered = nrProductList().filter(function (p) {
      return String(p.name || '').toLowerCase().includes(q)
        || String(p.code || '').toLowerCase().includes(q)
        || String(p.company || '').toLowerCase().includes(q)
        || String(p.display || '').toLowerCase().includes(q);
    }).slice(0, 12);

    if (!filtered.length) {
      $dd.html('<div class="dropdown-item text-muted">No products found</div>').show();
      syncNrProductDropdown();
      return;
    }

    let html = '';
    filtered.forEach(function (p, idx) {
      html += `<a href="#" class="dropdown-item ${idx === 0 ? 'active' : ''}" data-code="${p.code}">
        <div class="fw-semibold">${p.name || p.display || p.code}</div>
        <div class="text-muted small">${p.code}${p.company ? ' · ' + p.company : ''}</div>
      </a>`;
    });
    $dd.html(html).show();
    syncNrProductDropdown();
  }

  function resetNrForm() {
    clearNrProductSelection();
    $('#nrSpecialRate').val('');
    $('#nrEffectiveFrom').val(new Date().toISOString().slice(0, 10));
    $('#nrDiscountPercent').val('0');
    $('#nrRemarks').val('');
  }

  function loadNrRates(customerCode) {
    const code = customerCode || $('#nrCustomerCode').val();
    const $tb = $('#nrRatesTable tbody');
    if (!code) {
      $tb.html('<tr><td colspan="6" class="text-center text-muted py-3">Select supplier first</td></tr>');
      $('#nrRatesHint').text('—');
      return;
    }
    $('#nrRatesHint').text('Loading...');
    $.get(`{{ url('/customer-discount/rates') }}/${encodeURIComponent(code)}`)
      .done(function (res) {
        const all = res.data || [];
        nrRatesCache = all.filter(function (r) {
          return String(r.rateType || 'Purchase') === 'Purchase';
        });
        renderNrRates(nrRatesCache);
        $('#nrRatesHint').text(nrRatesCache.length + ' rate' + (nrRatesCache.length === 1 ? '' : 's'));
      })
      .fail(function () {
        nrRatesCache = [];
        $tb.html('<tr><td colspan="6" class="text-center text-danger py-3">Failed to load rates</td></tr>');
        $('#nrRatesHint').text('Failed');
      });
  }

  function renderNrRates(rates) {
    const $tb = $('#nrRatesTable tbody').empty();
    if (!rates.length) {
      $tb.html('<tr><td colspan="6" class="text-center text-muted py-3">No purchase rates for this supplier</td></tr>');
      return;
    }
    rates.forEach(function (r, idx) {
      const variantLabel = (r.variant_name || r.variantName)
        ? ((r.variant_name || r.variantName) + (r.variant_value || r.variantValue ? ': ' + (r.variant_value || r.variantValue) : ''))
        : (r.variantInfo || '—');
      $tb.append(`
        <tr>
          <td class="text-center">${idx + 1}</td>
          <td>
            <div class="fw-semibold">${r.productName || r.productCode}</div>
            <small class="text-muted">${r.productCode}${r.companyName ? ' · ' + r.companyName : ''}</small>
          </td>
          <td>${variantLabel}</td>
          <td class="text-end fw-semibold">${nrFmt(r.specialRate)}</td>
          <td>${r.effective_from || '-'}</td>
          <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm btn-nr-remove" data-id="${r.id}" title="Remove">
              <i class="bi bi-trash"></i>
            </button>
          </td>
        </tr>
      `);
    });
  }

  function openNetRateModal() {
    const code = $('#supplierCode').val();
    if (!code) {
      if (typeof showWarning === 'function') {
        showWarning('Select Supplier', 'Please select a supplier first');
      } else {
        Swal.fire({ icon: 'warning', title: 'Select Supplier', text: 'Please select a supplier first' });
      }
      return;
    }
    const $opt = $('#supplierCode option[value="' + code + '"]');
    const name = $opt.data('name') || $opt.data('business') || $('#supplierSearch').val() || code;
    const label = name + ' (' + code + ')';
    $('#nrCustomerCode').val(code);
    $('#nrCustomerLabel').val(label);
    resetNrForm();
    loadNrRates(code);
    bootstrap.Modal.getOrCreateInstance(document.getElementById('netRateModal')).show();
    setTimeout(function () { $('#nrProductSearch').focus(); }, 250);
  }

  $('#addNetRateLink').on('click', function (e) {
    e.preventDefault();
    openNetRateModal();
  });

  $('#nrProductSearch').on('input', function () {
    if (!$('#nrProductCode').val()) {
      /* keep typing */
    } else {
      $('#nrProductCode').val('');
      syncNrClearBtn();
    }
    filterNrProducts($(this).val());
  });

  $('#nrProductSearch').on('focus', function () {
    if ($(this).val().trim() && !$('#nrProductCode').val()) {
      filterNrProducts($(this).val());
    }
  });

  $('#nrProductSearch').on('keydown', function (e) {
    const $dd = $('#nrProductDropdown');
    const $items = $dd.find('.dropdown-item[data-code]');
    const $active = $items.filter('.active');
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      if (!$items.length) return;
      const idx = $active.length ? Math.min($items.index($active) + 1, $items.length - 1) : 0;
      $items.removeClass('active').eq(idx).addClass('active');
      syncNrProductDropdown();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      if (!$active.length) return;
      const idx = Math.max($items.index($active) - 1, 0);
      $items.removeClass('active').eq(idx).addClass('active');
      syncNrProductDropdown();
    } else if (e.key === 'Enter') {
      e.preventDefault();
      const $sel = $dd.is(':visible') ? $items.filter('.active') : $();
      if ($sel.length && $sel.data('code')) {
        selectNrProduct($sel.data('code'));
      } else if (!$('#nrProductCode').val() && String($(this).val() || '').trim()) {
        if (typeof showWarning === 'function') {
          showWarning('Product not found', 'No product matches your search.');
        }
      }
    } else if (e.key === 'Escape') {
      hideNrProductDropdown();
    }
  });

  $('#nrProductDropdown').on('mousedown', '.dropdown-item[data-code]', function (e) {
    e.preventDefault();
    selectNrProduct($(this).data('code'));
  });

  $('#btnClearNrProduct').on('click', function () {
    clearNrProductSelection();
    $('#nrProductSearch').focus();
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#nrProductSearch, #nrProductDropdown, #btnClearNrProduct').length) {
      hideNrProductDropdown();
    }
  });

  $('#nrVariantSelect').on('change', function () {
    const code = $('#nrProductCode').val();
    const p = nrProductByCode(code);
    let base = p ? Number(p.cost != null ? p.cost : (p.rate || 0)) : 0;
    const $v = $(this).find('option:selected');
    if ($v.val()) {
      base += parseFloat($v.data('add-cost')) || 0;
    }
    $('#nrSpecialRate').val(Number(base).toFixed(2));
  });

  $('#nrFormResetBtn').on('click', function () {
    resetNrForm();
    $('#nrProductSearch').focus();
  });

  $('#nrRatesTable').on('click', '.btn-nr-remove', function () {
    const id = $(this).data('id');
    Swal.fire({
      icon: 'warning',
      title: 'Remove net rate?',
      text: 'This will deactivate the saved purchase rate.',
      showCancelButton: true,
      confirmButtonText: 'Remove',
      confirmButtonColor: '#dc3545',
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.ajax({
        url: `{{ url('/customer-discount') }}/${id}`,
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      })
        .done(function (res) {
          if (typeof showSuccess === 'function') showSuccess(res.message || 'Rate removed');
          loadNrRates();
          const supplierCode = $('#supplierCode').val();
          if (supplierCode && typeof updateProductsForSupplier === 'function') {
            updateProductsForSupplier(supplierCode);
          }
        })
        .fail(function (xhr) {
          Swal.fire({ icon: 'error', title: 'Failed', text: xhr.responseJSON?.message || 'Could not remove rate' });
        });
    });
  });

  $('#netRateForm').on('submit', function (e) {
    e.preventDefault();
    const customerCode = $('#nrCustomerCode').val();
    const productCode = $('#nrProductCode').val();
    const specialRate = $('#nrSpecialRate').val();
    if (!customerCode || !productCode || specialRate === '' || specialRate == null) {
      if (typeof showWarning === 'function') {
        showWarning('Required', 'Supplier, product and net rate are required');
      } else {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Supplier, product and net rate are required' });
      }
      return;
    }
    const $v = $('#nrVariantSelect option:selected');
    const payload = {
      customerCode: customerCode,
      productCode: productCode,
      workName: null,
      specialRate: specialRate,
      rateType: 'Purchase',
      discountPercent: $('#nrDiscountPercent').val() || 0,
      remarks: $('#nrRemarks').val() || '',
      effective_from: $('#nrEffectiveFrom').val(),
      variant_id: $v.val() || null,
      variant_name: $v.data('name') || null,
      variant_value: $v.data('value') || null,
    };

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    $.post(`{{ route('customerDiscount.store') }}`, payload)
      .done(function (res) {
        if (typeof showSuccess === 'function') {
          showSuccess(res.message || 'Net rate saved');
        } else {
          Swal.fire({ icon: 'success', title: 'Saved', text: res.message || 'Net rate saved' });
        }
        resetNrForm();
        loadNrRates();
        if (typeof updateProductsForSupplier === 'function') {
          updateProductsForSupplier(customerCode);
        }
      })
      .fail(function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'Failed',
          text: xhr.responseJSON?.message || 'Could not save net rate',
        });
      });
  });
})();
</script>

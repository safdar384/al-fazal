{{-- Shared: warn when leaving a product line / adding next row if qty exceeds available stock --}}
window.getProductAvailableStock = function (productCode, variantId) {
  if (!productCode) return 0;
  if (variantId) {
    const map = (typeof window.productVariants !== 'undefined' && window.productVariants)
      ? window.productVariants
      : (typeof productVariants !== 'undefined' ? productVariants : {});
    const list = map[productCode] || [];
    const v = list.find(function (x) { return String(x.id) === String(variantId); });
    return v ? (parseFloat(v.stockQty) || 0) : 0;
  }
  const products = (typeof window.allProducts !== 'undefined' && window.allProducts)
    ? window.allProducts
    : (typeof allProducts !== 'undefined' ? allProducts : []);
  const p = products.find(function (x) { return x.code === productCode; });
  return p ? (parseFloat(p.stockQty) || 0) : 0;
};

/**
 * @param {HTMLElement|jQuery} rowEl
 * @returns {boolean} true if stock is enough (or row incomplete)
 */
window.assertProductRowStock = function (rowEl) {
  const $row = rowEl && rowEl.jquery ? rowEl : $(rowEl);
  if (!$row.length) return true;

  const productCode = $row.find('.productCode').val();
  if (!productCode) return true;

  const $variantWrap = $row.find('.variantWrapper');
  const needsVariant = $variantWrap.length && !$variantWrap.hasClass('d-none');
  const variantId = $row.find('.variantSelect').val() || null;
  if (needsVariant && !variantId) return true;

  const qty = parseFloat($row.find('.qty').val()) || 0;
  if (qty <= 0) return true;

  let used = 0;
  $('#productTable tbody tr').each(function () {
    const code = $(this).find('.productCode').val();
    if (code !== productCode) return;
    const vid = $(this).find('.variantSelect').val() || null;
    if (String(vid || '') !== String(variantId || '')) return;
    used += parseFloat($(this).find('.qty').val()) || 0;
  });

  const available = window.getProductAvailableStock(productCode, variantId);
  if (used <= available + 0.0001) return true;

  const label = ($row.find('.productCode option:selected').text() || productCode).trim();
  const msg = 'Stock less hai. Available: ' + available + (label ? ' — ' + label : '');
  if (typeof showWarning === 'function') {
    showWarning('Less Stock', msg);
  } else if (typeof Swal !== 'undefined') {
    Swal.fire({ icon: 'warning', title: 'Less Stock', text: msg, confirmButtonColor: '#0f2747' });
  } else {
    alert(msg);
  }
  $row.find('.qty').focus().select();
  return false;
};

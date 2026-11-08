{{-- Shared: product / variant uniqueness on sale & cash invoice product grids --}}
window.productHasVariants = function (code) {
  if (!code) return false;
  const products = (typeof window.allProducts !== 'undefined' && window.allProducts)
    ? window.allProducts
    : (typeof allProducts !== 'undefined' ? allProducts : []);
  const p = products.find(function (x) { return String(x.code) === String(code); });
  if (p && p.hasVariants) return true;
  const map = (typeof window.productVariants !== 'undefined' && window.productVariants)
    ? window.productVariants
    : (typeof productVariants !== 'undefined' ? productVariants : {});
  const list = map[code] || map[String(code)] || [];
  return list.length > 0;
};

/** Variant ids already used for this product on other rows */
window.usedVariantIdsForProduct = function (code, exceptRow) {
  const ids = [];
  if (!code) return ids;
  const exceptEl = exceptRow && exceptRow.jquery ? exceptRow[0] : exceptRow;
  $('#productTable tbody tr').each(function () {
    if (exceptEl && this === exceptEl) return;
    if (String($(this).find('.productCode').val() || '') !== String(code)) return;
    const vid = $(this).find('.variantSelect').val();
    if (vid) ids.push(String(vid));
  });
  return ids;
};

/**
 * Non-variant products: same productCode = duplicate.
 * Variant products: allowed multiple times; uniqueness is checked per variant.
 */
window.productAlreadyOnInvoice = function (code, $row) {
  if (!code) return false;
  if (window.productHasVariants(code)) return false;
  let used = false;
  const rowEl = $row && $row.jquery ? $row[0] : $row;
  $('#productTable tbody tr').each(function () {
    if (rowEl && this === rowEl) return;
    if (String($(this).find('.productCode').val() || '') === String(code)) used = true;
  });
  return used;
};

window.productVariantAlreadyOnInvoice = function (code, variantId, $row) {
  if (!code || !variantId) return false;
  let used = false;
  const rowEl = $row && $row.jquery ? $row[0] : $row;
  $('#productTable tbody tr').each(function () {
    if (rowEl && this === rowEl) return;
    if (String($(this).find('.productCode').val() || '') !== String(code)) return;
    if (String($(this).find('.variantSelect').val() || '') === String(variantId)) used = true;
  });
  return used;
};

/**
 * On variant select: block if same product+variant already on another line.
 * @returns {boolean} true if OK to keep selection
 */
window.assertUniqueProductVariant = function ($row, variantId) {
  const code = $row.find('.productCode').val();
  if (!variantId || !code) return true;
  if (!window.productVariantAlreadyOnInvoice(code, variantId, $row)) return true;
  showWarning('Duplicate variant', 'This product variant is already added. Choose a different variant.');
  return false;
};

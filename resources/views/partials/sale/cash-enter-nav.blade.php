window.focusNextCashField = function (current) {
  const fields = Array.from(document.querySelectorAll('#main input, #main select, #main textarea, #productTable button[title="Add Row"], #btnSaveInvoice')).filter(function (el) {
    if (el.disabled || el.readOnly) return false;
    if ((el.type || '') === 'hidden') return false;
    if (el.closest('.d-none, .productDropdown, .modal')) return false;
    return el.offsetParent !== null || el === document.activeElement;
  });

  const index = fields.indexOf(current);
  const next = index >= 0 ? fields[index + 1] : null;
  const target = next || document.getElementById('btnSaveInvoice');
  if (!target) return;
  target.focus();
  if (target.tagName === 'INPUT' && target.type !== 'date' && typeof target.select === 'function') {
    target.select();
  }
};

document.addEventListener('keydown', function (e) {
  if (e.key !== 'Enter') return;
  const el = e.target;
  if (!el || !el.closest || !el.closest('#main')) return;
  if (el.tagName === 'TEXTAREA' && e.shiftKey) return;
  if (el.classList && (el.classList.contains('productSearch') || el.id === 'customerSearch')) return;
  const tag = el.tagName;
  const isAddRowBtn = el.closest && el.closest('button[title="Add Row"]');
  const isField = tag === 'INPUT' || tag === 'SELECT' || tag === 'TEXTAREA' || el.id === 'btnSaveInvoice' || isAddRowBtn;
  if (!isField) return;

  if (el.id === 'btnSaveInvoice') {
    e.preventDefault();
    el.click();
    return;
  }

  // Enter on "+" → next row (stock check inside addRow)
  if (isAddRowBtn) {
    e.preventDefault();
    if (typeof addRow === 'function') addRow(isAddRowBtn);
    return;
  }

  const row = el.closest('#productTable tr');
  if (row && el.classList && el.classList.contains('discount')) {
    e.preventDefault();
    if (typeof window.assertProductRowStock === 'function' && !window.assertProductRowStock(row)) {
      return;
    }
    // Last product row: Enter on discount opens next row
    const isLast = row === document.querySelector('#productTable tbody tr:last-child');
    if (isLast && typeof addRow === 'function') {
      addRow(row.querySelector('button[title="Add Row"]') || row);
      return;
    }
    window.focusNextCashField(el);
    return;
  }

  // Leaving product row toward outside fields → stock check first
  if (row && typeof window.assertProductRowStock === 'function') {
    const fields = Array.from(document.querySelectorAll('#main input, #main select, #main textarea, #productTable button[title="Add Row"], #btnSaveInvoice')).filter(function (f) {
      if (f.disabled || f.readOnly) return false;
      if ((f.type || '') === 'hidden') return false;
      if (f.closest('.d-none, .productDropdown, .modal')) return false;
      return f.offsetParent !== null || f === document.activeElement;
    });
    const index = fields.indexOf(el);
    const next = index >= 0 ? fields[index + 1] : null;
    if (next && !row.contains(next) && !window.assertProductRowStock(row)) {
      e.preventDefault();
      return;
    }
  }

  e.preventDefault();
  window.focusNextCashField(el);
});

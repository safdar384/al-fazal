let activeForm = null;

/**
 * Float a dropdown panel under an input using position:fixed so it appears
 * above scroll/overflow containers (invoice product grids).
 */
function erpPositionFloatingDropdown(anchorEl, panelEl, options) {
    if (!anchorEl || !panelEl) return;
    options = options || {};
    const minW = options.minWidth || 260;
    const maxW = options.maxWidth || null;
    const maxH = options.maxHeight || 320;
    const rect = anchorEl.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    let width = Math.max(rect.width, minW);
    if (maxW) {
        width = Math.min(width, maxW);
    }
    let left = rect.left;
    if (left + width > vw - 8) {
        left = Math.max(8, vw - width - 8);
    }
    let top = rect.bottom + 4;
    panelEl.style.position = 'fixed';
    panelEl.style.left = left + 'px';
    panelEl.style.top = top + 'px';
    panelEl.style.width = width + 'px';
    panelEl.style.minWidth = minW + 'px';
    panelEl.style.maxHeight = maxH + 'px';
    panelEl.style.zIndex = String(options.zIndex || 10650);
    panelEl.style.right = 'auto';
    panelEl.style.marginTop = '0';
    panelEl.style.overflowY = 'auto';

    const estH = Math.min(panelEl.scrollHeight || 200, maxH);
    if (top + estH > vh - 8 && rect.top > estH + 8) {
        top = Math.max(8, rect.top - estH - 4);
        panelEl.style.top = top + 'px';
    }
}

function erpResetFloatingDropdown(panelEl) {
    if (!panelEl || !panelEl.style) return;
    panelEl.style.position = '';
    panelEl.style.top = '';
    panelEl.style.left = '';
    panelEl.style.width = '';
    panelEl.style.minWidth = '';
    panelEl.style.maxHeight = '';
    panelEl.style.zIndex = '';
    panelEl.style.right = '';
    panelEl.style.marginTop = '';
    panelEl.style.overflowY = '';
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.erp-invoice-product-wrap').forEach(function (wrap) {
        wrap.addEventListener(
            'scroll',
            function () {
                wrap.querySelectorAll('.productDropdown').forEach(function (drop) {
                    if (window.getComputedStyle(drop).display === 'none') {
                        return;
                    }
                    drop.style.display = 'none';
                    erpResetFloatingDropdown(drop);
                });
            },
            { passive: true }
        );
    });
});

/**
 * Mobile / tablet: close slide-out sidebar after choosing a real destination link.
 * AdminLTE PushMenu uses body.sidebar-open / sidebar-collapse under 992px.
 */
(function () {
    const SIDEBAR_BREAKPOINT = 992;

    function collapseSidebarMobile() {
        if (window.innerWidth >= SIDEBAR_BREAKPOINT) {
            return;
        }
        document.body.classList.remove('sidebar-open');
        document.body.classList.add('sidebar-collapse');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.app-sidebar a.nav-link[href]').forEach(function (anchor) {
            const href = anchor.getAttribute('href');
            if (!href || href === '#' || href.startsWith('javascript:')) {
                return;
            }
            anchor.addEventListener('click', function () {
                window.requestAnimationFrame(collapseSidebarMobile);
            });
        });
    });
})();

/**
 * Show confirm modal for any action (Delete / Save / Update)
 * Supports passing string (formId) or object with full options
 * options = {
 *   formId: "myForm",
 *   title: "Confirm Save",
 *   message: "Do you want to save?",
 *   okText: "Yes, Save",
 *   okClass: "btn-success",
 *   icon: "bi bi-check2-circle"
 * }
 */
function showConfirm(options) {
    // If string is passed, use default save modal
    if (typeof options === 'string') {
        options = {
            formId: options,
            title: 'Confirm Save',
            message: 'Do you want to save this record?',
            okText: 'Yes, Save',
            okClass: 'btn-success',
            icon: 'bi bi-check2-circle'
        };
    }

    activeForm = document.getElementById(options.formId);

    // Set modal content
    document.getElementById('confirmTitle').innerText = options.title || 'Confirm';
    document.getElementById('confirmMessage').innerText = options.message || 'Are you sure?';

    const confirmBtn = document.getElementById('confirmOk');
    confirmBtn.innerHTML = `<i class="${options.icon || 'bi bi-check-circle'} me-1"></i> ${options.okText || 'Confirm'}`;
    confirmBtn.className = `btn px-4 ${options.okClass || 'btn-primary'}`;

    // Set header color based on button
    const header = document.getElementById('confirmHeader');
    header.className = `modal-header text-white ${options.okClass.replace('btn', 'bg') || 'bg-primary'}`;

    // Remove old click events and add new
    const newBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);

    newBtn.addEventListener('click', () => {
        if (activeForm) {
            activeForm.submit();
        }
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    });

    // Show modal
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

/**
 * Show delete confirmation using SweetAlert2
 * @param {string} formId - ID of the form to submit
 * @param {string} title - Modal title (optional)
 * @param {string} message - Confirmation message (optional)
 */
function confirmDelete(formId, title = 'Delete Confirmation', message = 'Are you sure you want to delete this item?') {
    Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash3 me-1"></i> Yes, Delete',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            }
        }
    });
}

/**
 * Show popup message using SweetAlert2
 * @param {string} type - 'success', 'error', 'warning', 'info', 'question'
 * @param {string} title - Message title
 * @param {string} text - Message text (optional)
 * @param {number} timer - Auto close timer in ms (optional, default 2000)
 */
function showPopup(type, title, text = '', timer = 2000) {
    const config = {
        icon: type,
        title: title,
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true,
        position: 'top-end',
        toast: true,
        showCloseButton: true
    };
    
    if (text) {
        config.text = text;
    }
    
    Swal.fire(config);
}

/**
 * Show success message
 */
function showSuccess(title, text = '', timer = 2000) {
    showPopup('success', title, text, timer);
}

/**
 * Show error message
 */
function showError(title, text = '', timer = 3000) {
    showPopup('error', title, text, timer);
}

/**
 * Show warning message
 */
function showWarning(title, text = '', timer = 2500) {
    showPopup('warning', title, text, timer);
}

/**
 * Show info message
 */
function showInfo(title, text = '', timer = 2000) {
    showPopup('info', title, text, timer);
}

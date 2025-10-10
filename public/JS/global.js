let activeForm = null;

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

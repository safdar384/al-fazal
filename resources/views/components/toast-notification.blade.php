<script>
// Professional Toast Notification System
function showToast(type, title, message, duration = 4000) {
  // Create toast container if not exists
  if(!$('#toastContainer').length) {
    $('body').append(`<div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>`);
  }
  
  const toastId = 'toast_' + Date.now();
  const bgColor = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#17a2b8';
  const iconClass = type === 'success' ? 'bi-check-circle-fill' : type === 'error' ? 'bi-exclamation-circle-fill' : type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill';
  
  const toastHtml = `
    <div id="${toastId}" class="toast-notification" style="
      background: ${bgColor};
      color: white;
      padding: 16px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      margin-bottom: 10px;
      min-width: 320px;
      max-width: 400px;
      animation: slideIn 0.3s ease-in-out;
    ">
      <div style="display: flex; align-items: flex-start; gap: 12px;">
        <i class="bi ${iconClass}" style="font-size: 20px; flex-shrink: 0; margin-top: 2px;"></i>
        <div style="flex: 1;">
          <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px;">${title}</div>
          <div style="font-size: 13px; opacity: 0.95; line-height: 1.4;">${message}</div>
        </div>
        <button onclick="$('#${toastId}').fadeOut(300, function() { $(this).remove(); })" style="
          background: none;
          border: none;
          color: white;
          font-size: 18px;
          cursor: pointer;
          padding: 0;
          width: 24px;
          height: 24px;
          display: flex;
          align-items: center;
          justify-content: center;
          opacity: 0.8;
          transition: opacity 0.2s;
        " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </div>
  `;
  
  $('#toastContainer').append(toastHtml);
  
  // Auto remove after duration
  setTimeout(() => {
    $(`#${toastId}`).fadeOut(300, function() {
      $(this).remove();
    });
  }, duration);
}

function showToastSuccess(title, message) {
  showToast('success', title, message, 4000);
}

function showToastError(title, message) {
  showToast('error', title, message, 5000);
}

function showToastWarning(title, message) {
  showToast('warning', title, message, 4000);
}

function showToastInfo(title, message) {
  showToast('info', title, message, 4000);
}

// Add CSS animations if not already present
if (!document.querySelector('style[data-toast-animations]')) {
  const style = document.createElement('style');
  style.setAttribute('data-toast-animations', 'true');
  style.textContent = `
    @keyframes slideIn {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
  `;
  document.head.appendChild(style);
}
</script>

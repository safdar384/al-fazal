<footer class="app-footer">
    <div class="float-end d-none d-sm-inline">
        Version 1.0
    </div>
    <strong>
        Copyright &copy; {{ date('Y') }}&nbsp;
        <a href="{{ url('/') }}" class="text-decoration-none">{{ config('app.name', 'TradeGate') }}</a>.
    </strong>
    All rights reserved.
</footer>
<!--end::Footer-->
</div>
<!--end::App Wrapper-->

<!-- Notification Settings Modal -->
<div class="modal fade" id="notificationSettings" tabindex="-1" aria-labelledby="notificationSettingsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="notificationSettingsLabel">
                    <i class="bi bi-bell-slash me-2"></i>Notification Preferences
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body notification-settings-modal">
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Notification Types</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="notifyStockAlert" checked>
                        <label class="form-check-label" for="notifyStockAlert">
                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>Stock Alerts
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="notifyPayment" checked>
                        <label class="form-check-label" for="notifyPayment">
                            <i class="bi bi-cash-coin text-success me-2"></i>Payment Notifications
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="notifyInvoice" checked>
                        <label class="form-check-label" for="notifyInvoice">
                            <i class="bi bi-receipt text-info me-2"></i>Invoice Updates
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="notifyOverdue" checked>
                        <label class="form-check-label" for="notifyOverdue">
                            <i class="bi bi-exclamation-circle text-danger me-2"></i>Overdue Payments
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Notification Methods</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="enableToast" checked>
                        <label class="form-check-label" for="enableToast">
                            <i class="bi bi-chat-dots text-primary me-2"></i>Toast Notifications
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="enableSound" checked>
                        <label class="form-check-label" for="enableSound">
                            <i class="bi bi-volume-up text-info me-2"></i>Sound Alerts
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="enableBrowser" checked>
                        <label class="form-check-label" for="enableBrowser">
                            <i class="bi bi-browser-edge text-secondary me-2"></i>Browser Notifications
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Alert Level</h6>
                    <div class="btn-group-vertical w-100" role="group">
                        <input type="radio" class="btn-check" name="alertLevel" id="alertAll" value="all" checked>
                        <label class="btn btn-outline-primary text-start" for="alertAll">
                            <i class="bi bi-check me-2"></i>All Notifications
                        </label>
                        <input type="radio" class="btn-check" name="alertLevel" id="alertImportant" value="important">
                        <label class="btn btn-outline-primary text-start" for="alertImportant">
                            <i class="bi bi-exclamation me-2"></i>Important Only
                        </label>
                        <input type="radio" class="btn-check" name="alertLevel" id="alertCritical" value="critical">
                        <label class="btn btn-outline-primary text-start" for="alertCritical">
                            <i class="bi bi-exclamation-triangle me-2"></i>Critical Only
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveNotificationPrefs">
                    <i class="bi bi-check-circle me-2"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- User Settings Modal -->
<div class="modal fade" id="userSettings" tabindex="-1" aria-labelledby="userSettingsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="userSettingsLabel">
                    <i class="bi bi-gear me-2"></i>Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Display Theme</label>
                    <select class="form-select">
                        <option selected>Light Mode</option>
                        <option>Dark Mode</option>
                        <option>Auto</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Language</label>
                    <select class="form-select">
                        <option selected>English</option>
                        <option>Urdu</option>
                        <option>Arabic</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!--begin::Required Scripts-->
<!--begin::OverlayScrollbars-->
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>

<!--begin::Popper.js (Required for Bootstrap 5)-->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>

<!--begin::Bootstrap 5-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

<!--begin::AdminLTE-->
<script src="{{ asset('adminlte/dist/js/adminlte.js') }}"></script>

<!--begin::OverlayScrollbars Configure-->
<script>
const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
const Default = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'leave',
    scrollbarClickScroll: true,
};

document.addEventListener('DOMContentLoaded', function () {
    const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
    if (sidebarWrapper && typeof OverlayScrollbarsGlobal !== 'undefined') {
        OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
                theme: Default.scrollbarTheme,
                autoHide: Default.scrollbarAutoHide,
                clickScroll: Default.scrollbarClickScroll,
            },
        });
    }
});
</script>

<!-- Notification System Script -->
<script>
    $(document).ready(function() {
        // Configure Toastr options
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Audio notification function
        function playNotificationSound() {
            if ($('#enableSound').is(':checked')) {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            }
        }

        // Ring bell animation
        function ringBell() {
            const bell = $('#notificationBell i');
            bell.addClass('notification-bell-animate');
            setTimeout(() => bell.removeClass('notification-bell-animate'), 500);
        }

        // Show notification toast
        function showNotificationToast(title, message, type) {
            if ($('#enableToast').is(':checked')) {
                toastr[type](message, title);
            }
        }

        // Mark all notifications as read
        $('#markAllRead').click(function(e) {
            e.preventDefault();
            $('.notification-item').removeClass('unread').addClass('read');
            $('#notificationCount').text('0').hide();
            showNotificationToast('Success', 'All notifications marked as read', 'success');
        });

        // Save notification preferences
        $('#saveNotificationPrefs').click(function() {
            localStorage.setItem('notificationPrefs', JSON.stringify({
                enableToast: $('#enableToast').is(':checked'),
                enableSound: $('#enableSound').is(':checked'),
                enableBrowser: $('#enableBrowser').is(':checked'),
                alertLevel: $('input[name="alertLevel"]:checked').val()
            }));
            
            showNotificationToast('Settings', 'Notification preferences saved', 'info');
            bootstrap.Modal.getInstance(document.getElementById('notificationSettings')).hide();
        });

        // Load preferences from localStorage
        function loadPreferences() {
            const prefs = JSON.parse(localStorage.getItem('notificationPrefs'));
            if (prefs) {
                $('#enableToast').prop('checked', prefs.enableToast);
                $('#enableSound').prop('checked', prefs.enableSound);
                $('#enableBrowser').prop('checked', prefs.enableBrowser);
                $(`input[name="alertLevel"][value="${prefs.alertLevel}"]`).prop('checked', true);
            }
        }

        // Test notification function
        function testNotification() {
            ringBell();
            playNotificationSound();
            showNotificationToast('Test Notification', 'This is a test notification', 'info');
        }

        // Initialize on page load
        loadPreferences();

        // Simulate notifications (remove in production, connect to real API)
        window.addNotification = function(title, message, type = 'info', icon = 'bi-bell-fill') {
            ringBell();
            playNotificationSound();
            showNotificationToast(title, message, type);
            
            // Add notification to list
            const notificationHtml = `
                <a href="#" class="dropdown-item notification-item unread">
                    <div class="d-flex align-items-start p-2">
                        <div class="me-3">
                            <span class="notification-dot" style="background-color: ${getColorByType(type)}"></span>
                            <i class="bi ${icon} fs-5" style="color: ${getColorByType(type)}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 small fw-semibold">${title}</h6>
                            <p class="mb-1 small text-muted">${message}</p>
                            <p class="mb-0 small text-muted">
                                <i class="bi bi-clock"></i> just now
                            </p>
                        </div>
                    </div>
                </a>
                <div class="dropdown-divider" style="margin: 0;"></div>
            `;
            
            $('#notificationBody').prepend(notificationHtml);
            updateNotificationCount();
        };

        function getColorByType(type) {
            const colors = {
                'success': '#198754',
                'warning': '#ffc107',
                'danger': '#dc3545',
                'info': '#0dcaf0'
            };
            return colors[type] || '#0dcaf0';
        }

        function updateNotificationCount() {
            const unreadCount = $('.notification-item.unread').length;
            if (unreadCount > 0) {
                $('#notificationCount').text(unreadCount).show();
            } else {
                $('#notificationCount').hide();
            }
        }

        // Test notification on page load (remove in production)
        setTimeout(() => {
            // Example: addNotification('New Sale', 'Sale invoice INV-001 created', 'success', 'bi-receipt-fill');
        }, 2000);
    });
</script>

<!--begin::Page Specific Scripts-->
@stack('scripts')

</body>
</html>

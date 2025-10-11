<footer class="app-footer">
    <div class="float-end d-none d-sm-inline">
        Version 1.0
    </div>
    <strong>
        Copyright &copy; {{ date('Y') }}&nbsp;
        <a href="{{ url('/') }}" class="text-decoration-none">{{ config('app.name', 'TradGate') }}</a>.
    </strong>
    All rights reserved.
</footer>
<!--end::Footer-->
</div>
<!--end::App Wrapper-->

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

<!--begin::Page Specific Scripts-->
@stack('scripts')

</body>
</html>

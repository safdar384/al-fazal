<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <title>{{ config('app.name', 'TradeGate') }} - @yield('title', 'Dashboard')</title>
    
    <!--begin::Fonts-->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/source-sans-3.css') }}" />
    
    <!--begin::Bootstrap Icons-->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" />
    
    <!--begin::AdminLTE CSS-->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />

    <!--begin::ERP responsive layer (after AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('CSS/erp-responsive.css') }}" />
    
    <!--begin::OverlayScrollbars-->
    <link rel="stylesheet" href="{{ asset('vendor/overlayscrollbars/overlayscrollbars.min.css') }}" />
    
    <!--begin::Select2 CSS-->
    <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}" />
    
    <!--begin::DataTables CSS-->
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}" />
    
    <!--begin::Toastr Notification CSS-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css" />
    
    <!--begin::jQuery (Required for Select2 & DataTables)-->
    <script src="{{ asset('vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    
    <!--begin::Select2 JS-->
    <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
    
    <!--begin::DataTables JS-->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
    
    <!--begin::SweetAlert2-->
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <!--begin::Toastr JS-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

    <!--begin::Page Specific Styles-->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    @stack('styles')

    <style>
        .form-label {
            font-weight: 500;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .select2-container .select2-selection--single {
            height: 31px !important;
            padding: 2px 10px;
            font-size: 0.875rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 29px;
        }
        .dropdown-menu {
            min-width: max-content;
            max-height: 200px;
            overflow-y: auto;
        }
        .dropdown-menu li a {
            white-space: nowrap;
        }
        
        /* Sidebar Enhancements - Blue/White Theme */
        .app-sidebar {
            background-color: #0f2747 !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            --lte-sidebar-color: #dbe7ff;
            --lte-sidebar-hover-color: #ffffff;
            --lte-sidebar-active-color: #ffffff;
            --lte-sidebar-hover-bg: rgba(255, 255, 255, 0.08);
            --lte-sidebar-menu-active-bg: rgba(59, 130, 246, 0.28);
            --lte-sidebar-menu-active-color: #ffffff;
            --lte-sidebar-submenu-color: #c7dcff;
            --lte-sidebar-submenu-hover-color: #ffffff;
            --lte-sidebar-submenu-hover-bg: rgba(255, 255, 255, 0.07);
            --lte-sidebar-submenu-active-color: #ffffff;
            --lte-sidebar-submenu-active-bg: rgba(59, 130, 246, 0.22);
            --lte-sidebar-header-color: rgba(219, 231, 255, 0.68);
        }
        .sidebar-brand {
            background: linear-gradient(135deg, #12315a 0%, #1f4b87 100%);
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }
        .sidebar-brand .brand-image {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .sidebar-brand .brand-link {
            color: white !important;
            padding: 1.2rem 1rem;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .sidebar-brand .brand-text {
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }
        
        /* Nav Headers */
        .nav-header {
            padding: 1rem 1rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5) !important;
            text-transform: uppercase;
            margin-top: 0.5rem;
        }
        
        /* Active Menu Items */
        .nav-link.active {
            background: rgba(59, 130, 246, 0.26) !important;
            border-left: 3px solid #93c5fd;
        }
        
        /* Submenu Styling */
        .nav-treeview .nav-link {
            padding-left: 2.5rem !important;
        }
        .nav-treeview .nav-link:hover {
            background: rgba(255,255,255,0.08);
        }
        
        /* Header Styling */
        .app-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        /* Notification Badge */
        .navbar-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 0.6rem;
            padding: 0.2rem 0.35rem;
            background: #dc3545 !important;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        /* Dropdown Enhancements */
        .dropdown-menu-lg {
            min-width: 380px;
            max-height: 500px;
        }
        
        .dropdown-item:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        /* Search Bar Styling */
        .input-group-text {
            border-right: 0 !important;
        }
        .form-control:focus + .input-group-text,
        .input-group-text + .form-control:focus {
            border-color: #86b7fe;
        }
        .navbar-icon-link {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .navbar-icon-link:hover {
            background-color: #eaf2ff;
            color: #0d6efd;
        }
        .navbar-search-group {
            box-shadow: 0 4px 12px rgba(16, 42, 67, 0.08);
            border-radius: 10px;
            overflow: hidden;
        }
        .navbar-search-dropdown {
            position: absolute;
            top: 100%;
            width: min(560px, 100%);
            background: #fff;
            border: 1px solid #d9e8ff;
            border-radius: 12px;
            margin-top: 0.35rem;
            box-shadow: 0 14px 24px rgba(15, 39, 71, 0.16);
            z-index: 1050;
            max-height: 400px;
            overflow-y: auto;
        }
        .search-result-item {
            display: flex;
            gap: 0.65rem;
            align-items: flex-start;
            padding: 0.65rem 0.75rem;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #eef4ff;
        }
        .search-result-item:last-child {
            border-bottom: 0;
        }
        .search-result-item:hover {
            background: #f3f8ff;
        }
        
        /* Smooth Transitions */
        .nav-link, .dropdown-item {
            transition: all 0.2s ease;
        }

        /* Notification Item Styles */
        .notification-item {
            border-left: 3px solid transparent;
            background-color: #f8f9fa;
            transition: all 0.2s ease;
        }

        .notification-item:hover {
            background-color: #e9ecef;
            border-left-color: #667eea;
        }

        .notification-item.unread {
            background-color: #f0f7ff;
            border-left-color: #0d6efd;
            font-weight: 500;
        }

        .notification-item.read {
            opacity: 0.8;
        }

        .notification-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .notification-dot.warning {
            background-color: #ffc107;
        }

        .notification-dot.success {
            background-color: #198754;
        }

        .notification-dot.danger {
            background-color: #dc3545;
        }

        .notification-dot.info {
            background-color: #0dcaf0;
        }

        .notification-header {
            padding: 12px 16px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header-title {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .notification-actions {
            display: flex;
            gap: 5px;
        }

        .notification-actions .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .notification-body {
            max-height: 350px;
            overflow-y: auto;
        }

        .notification-footer {
            padding: 8px 16px;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            text-align: center;
        }

        .notification-settings-modal .modal-body {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Sound/Bell Animation */
        .notification-bell-animate {
            animation: ring 0.5s ease-in-out;
        }

        @keyframes ring {
            0% { transform: rotate(0deg); }
            15% { transform: rotate(15deg); }
            30% { transform: rotate(-15deg); }
            45% { transform: rotate(15deg); }
            60% { transform: rotate(-15deg); }
            75% { transform: rotate(15deg); }
            100% { transform: rotate(0deg); }
        }

        /* Toastr Custom Styling */
        .toast-container {
            z-index: 9999;
        }

        #toast-container > div {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 6px;
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary erp-ui">
    <div class="app-wrapper">
        <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body shadow-sm flex-column align-items-stretch">
            <div class="container-fluid">
                <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle menu">
                            <i class="bi bi-list fs-5"></i>
                        </a>
                    </li>
                    <li class="nav-item d-md-none">
                        <a href="{{ url('/dashboard') }}" class="nav-link" title="Home" aria-label="Home">
                            <i class="bi bi-house-door fs-5"></i>
                        </a>
                    </li>
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" href="#mobileSearchCollapse" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="mobileSearchCollapse" title="Search">
                            <i class="bi bi-search fs-5"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a href="{{ url('/dashboard') }}" class="nav-link">
                            <i class="bi bi-house-door me-1"></i> Home
                        </a>
                    </li>
                </ul>
                <!--end::Start Navbar Links-->
                
                <!--begin::Center Search (Desktop)-->
                <div class="d-none d-lg-flex flex-grow-1 justify-content-center px-3 position-relative">
                    <form class="d-flex" role="search" style="max-width: 560px; width: 100%;">
                        <div class="input-group navbar-search-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input id="navbarGlobalSearch" class="form-control border-start-0" type="search" placeholder="Search product, customer, invoice, order..." aria-label="Global Search" autocomplete="off">
                        </div>
                    </form>
                    <div id="navbarSearchDropdown" class="navbar-search-dropdown d-none"></div>
                </div>
                <!--end::Center Search-->
                
                <!--begin::End Navbar Links-->
                <ul class="navbar-nav ms-auto">
                    
                    <!--begin::Quick Actions-->
                    <li class="nav-item dropdown d-none d-md-block">
                        <a class="nav-link navbar-icon-link" href="#" data-bs-toggle="dropdown" title="Quick Actions">
                            <i class="bi bi-plus-circle fs-5"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow border-0">
                            <span class="dropdown-item dropdown-header fw-semibold">Quick Actions</span>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('product.create') }}" class="dropdown-item">
                                <i class="bi bi-box-seam me-2 text-primary"></i> New Product
                            </a>
                            <a href="{{ route('Customer.create') }}" class="dropdown-item">
                                <i class="bi bi-person-plus me-2 text-success"></i> New Customer
                            </a>
                            <a href="{{ route('Labour.create') }}" class="dropdown-item">
                                <i class="bi bi-person-badge me-2 text-info"></i> New Labour
                            </a>
                            <a href="{{ url('/SaleInvoice') }}" class="dropdown-item">
                                <i class="bi bi-receipt me-2 text-warning"></i> New Sale Invoice
                            </a>
                            <a href="{{ url('/cash-sale') }}" class="dropdown-item">
                                <i class="bi bi-cash-coin me-2 text-success"></i> New Cash Invoice
                            </a>
                        </div>
                    </li>
                    <!--end::Quick Actions-->

                    <!--begin::Quick Actions (mobile)-->
                    <li class="nav-item dropdown d-md-none">
                        <a class="nav-link navbar-icon-link" href="#" data-bs-toggle="dropdown" title="Quick Actions" aria-label="Quick Actions">
                            <i class="bi bi-plus-lg fs-5"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0">
                            <span class="dropdown-item dropdown-header fw-semibold">Quick Actions</span>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('product.create') }}" class="dropdown-item">
                                <i class="bi bi-box-seam me-2 text-primary"></i> New Product
                            </a>
                            <a href="{{ route('Customer.create') }}" class="dropdown-item">
                                <i class="bi bi-person-plus me-2 text-success"></i> New Customer
                            </a>
                            <a href="{{ route('Labour.create') }}" class="dropdown-item">
                                <i class="bi bi-person-badge me-2 text-info"></i> New Labour
                            </a>
                            <a href="{{ url('/SaleInvoice') }}" class="dropdown-item">
                                <i class="bi bi-receipt me-2 text-warning"></i> New Sale Invoice
                            </a>
                            <a href="{{ url('/cash-sale') }}" class="dropdown-item">
                                <i class="bi bi-cash-coin me-2 text-success"></i> New Cash Invoice
                            </a>
                        </div>
                    </li>
                    <!--end::Quick Actions (mobile)-->
                    
                    <!--begin::Notifications-->
                    <li class="nav-item dropdown" id="notificationDropdown">
                        <a class="nav-link navbar-icon-link" href="#" data-bs-toggle="dropdown" title="Notifications" id="notificationBell">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="badge badge-warning navbar-badge d-none" id="notificationCount">0</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow border-0" id="notificationMenu">
                            <div class="notification-header">
                                <span class="notification-header-title">
                                    <i class="bi bi-bell-fill me-2"></i>Notifications
                                </span>
                                <div class="notification-actions">
                                    <button class="btn btn-sm btn-link text-primary" id="refreshNotifications" title="Refresh">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button class="btn btn-sm btn-link text-primary" id="markAllRead" title="Mark all as read">
                                        <i class="bi bi-check2-all"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="notification-body" id="notificationBody">
                                <div class="p-3 text-center text-muted small">Loading notifications...</div>
                            </div>

                            <div class="notification-footer d-flex justify-content-between px-3">
                                @if(\Illuminate\Support\Facades\Route::has('partyCheques.index'))
                                <a href="{{ route('partyCheques.index') }}" class="text-primary small fw-semibold text-decoration-none">
                                    Party Cheques <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                                @endif
                                <a href="{{ route('order.summary') }}" class="text-primary small fw-semibold text-decoration-none">
                                    Orders <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </li>
                    <!--end::Notifications-->
                    
                    <!--begin::Fullscreen Toggle-->
                    <li class="nav-item d-none d-md-block">
                        <a class="nav-link" href="#" data-lte-toggle="fullscreen" title="Fullscreen">
                            <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen fs-5"></i>
                            <i data-lte-icon="minimize" class="bi bi-fullscreen-exit fs-5" style="display: none"></i>
                        </a>
                    </li>
                    <!--end::Fullscreen Toggle-->
                    
                    <!--begin::User Menu-->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                            <div class="user-image rounded-circle shadow-sm bg-gradient d-flex align-items-center justify-content-center text-white fw-bold me-2" 
                                 style="width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="d-none d-md-inline fw-semibold">{{ Auth::user()->name ?? 'User' }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow border-0">
                            <div class="dropdown-item dropdown-header text-center py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="rounded-circle shadow d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-2" 
                                     style="width: 80px; height: 80px; background: rgba(255,255,255,0.2);">
                                    <span style="font-size: 2rem;">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                                </div>
                                <h6 class="mb-0 text-white fw-bold">{{ Auth::user()->name ?? 'User' }}</h6>
                                <small class="text-white-50">{{ Auth::user()->email ?? 'Trade Management System' }}</small>
                                @if(Auth::check())
                                    <div class="mt-1">
                                        @if(Auth::user()->isAdmin())
                                            <span class="badge bg-light text-dark">Admin</span>
                                        @else
                                            <span class="badge bg-light text-dark">Client</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ url('/dashboard') }}" class="dropdown-item">
                                <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
                            </a>
                            @if(Auth::user()?->isAdmin())
                            <a href="{{ route('Customer.index') }}" class="dropdown-item">
                                <i class="bi bi-person me-2 text-info"></i> Customers
                            </a>
                            @endif
                            <a href="{{ route('reports.hub') }}" class="dropdown-item">
                                <i class="bi bi-gear me-2 text-secondary"></i> Reports
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        <div class="collapse w-100 d-lg-none border-top border-secondary-subtle bg-body px-2 py-2" id="mobileSearchCollapse">
            <div class="position-relative">
                <form class="d-flex" role="search">
                    <div class="input-group navbar-search-group w-100">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input id="navbarGlobalSearchMobile" class="form-control border-start-0" type="search" placeholder="Search product, customer, invoice..." aria-label="Global Search" autocomplete="off">
                    </div>
                </form>
                <div id="navbarSearchDropdownMobile" class="navbar-search-dropdown navbar-search-dropdown-mobile d-none"></div>
            </div>
        </div>
        </nav>
        <script>
            (function () {
                const searchInput = document.getElementById('navbarGlobalSearch');
                const searchInputMobile = document.getElementById('navbarGlobalSearchMobile');
                const searchDropdown = document.getElementById('navbarSearchDropdown');
                const searchDropdownMobile = document.getElementById('navbarSearchDropdownMobile');
                const notificationBody = document.getElementById('notificationBody');
                const notificationCount = document.getElementById('notificationCount');
                const refreshNotificationsBtn = document.getElementById('refreshNotifications');
                const markAllReadBtn = document.getElementById('markAllRead');

                const debounce = (fn, delay = 280) => {
                    let timer;
                    return (...args) => {
                        clearTimeout(timer);
                        timer = setTimeout(() => fn(...args), delay);
                    };
                };

                const esc = (value) => {
                    const div = document.createElement('div');
                    div.textContent = value ?? '';
                    return div.innerHTML;
                };

                const searchPanels = () => [searchDropdown, searchDropdownMobile].filter(Boolean);

                const hideSearchDropdowns = () => {
                    searchPanels().forEach((el) => {
                        el.innerHTML = '';
                        el.classList.add('d-none');
                    });
                };

                const showSearchHtml = (html) => {
                    searchPanels().forEach((el) => {
                        el.innerHTML = html;
                        el.classList.remove('d-none');
                    });
                };

                const renderSearch = (items) => {
                    if (!searchPanels().length) return;
                    if (!items.length) {
                        showSearchHtml('<div class="p-3 small text-muted">No matching records found.</div>');
                        return;
                    }

                    const html = items.map((item) => `
                        <a class="search-result-item" href="${esc(item.url)}">
                            <i class="bi bi-${esc(item.icon || 'search')} text-primary"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold small">${esc(item.title)}</div>
                                <div class="text-muted small">${esc(item.type)}${item.subtitle ? ' - ' + esc(item.subtitle) : ''}</div>
                            </div>
                        </a>
                    `).join('');
                    showSearchHtml(html);
                };

                const doSearch = debounce(async (q) => {
                    if (!searchPanels().length) return;
                    if (!q || q.length < 2) {
                        hideSearchDropdowns();
                        return;
                    }

                    try {
                        const response = await fetch(`{{ route('navbar.search') }}?q=${encodeURIComponent(q)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();
                        renderSearch(data.items || []);
                    } catch (error) {
                        showSearchHtml('<div class="p-3 small text-danger">Search failed. Try again.</div>');
                    }
                });

                const renderNotifications = (payload) => {
                    const items = payload.items || [];
                    if (!notificationBody) return;
                    if (notificationCount) {
                        notificationCount.textContent = payload.count || 0;
                        notificationCount.classList.toggle('d-none', !(payload.count > 0));
                    }

                    if (!items.length) {
                        notificationBody.innerHTML = '<div class="p-3 text-center small text-muted">No alerts.</div>';
                        return;
                    }

                    notificationBody.innerHTML = items.map((item) => {
                        const icon = esc(item.icon || 'journal-check');
                        const isChq = icon === 'bank2';
                        const msgHtml = isChq
                            ? `<p class="mb-1"><span class="fs-4 fw-bold text-danger">${esc(item.message)}</span></p>`
                            : `<p class="mb-1 small text-muted">${esc(item.message)}</p>`;
                        return `
                        <a href="${esc(item.url)}" class="dropdown-item notification-item unread">
                            <div class="d-flex align-items-start p-2">
                                <div class="me-3">
                                    <span class="notification-dot ${isChq ? 'danger' : 'warning'}"></span>
                                    <i class="bi bi-${icon} text-${isChq ? 'danger' : 'primary'} fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 small fw-semibold">${esc(item.title)}</h6>
                                    ${msgHtml}
                                    <p class="mb-0 small text-muted d-flex justify-content-between">
                                        <span><i class="bi bi-clock"></i> ${esc(item.time)}</span>
                                        <span class="fw-semibold">${esc(item.status)}</span>
                                    </p>
                                </div>
                            </div>
                        </a>`;
                    }).join('<div class="dropdown-divider m-0"></div>');
                };

                const loadNotifications = async () => {
                    if (!notificationBody) return;
                    try {
                        const response = await fetch(`{{ route('navbar.notifications') }}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await response.json();
                        renderNotifications(data);
                    } catch (error) {
                        notificationBody.innerHTML = '<div class="p-3 text-center small text-danger">Unable to load notifications.</div>';
                    }
                };

                const searchInputs = [searchInput, searchInputMobile].filter(Boolean);
                searchInputs.forEach((input) => {
                    input.addEventListener('input', (e) => doSearch(e.target.value.trim()));
                });
                document.addEventListener('click', (e) => {
                    const inside = searchInputs.some((input) => input === e.target)
                        || searchPanels().some((panel) => panel.contains(e.target));
                    if (!inside) {
                        hideSearchDropdowns();
                    }
                });

                if (refreshNotificationsBtn) {
                    refreshNotificationsBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        loadNotifications();
                    });
                }

                if (markAllReadBtn) {
                    markAllReadBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        document.querySelectorAll('#notificationBody .notification-item').forEach(el => {
                            el.classList.remove('unread');
                            el.classList.add('read');
                        });
                        if (notificationCount) {
                            notificationCount.textContent = '0';
                            notificationCount.classList.add('d-none');
                        }
                    });
                }

                // Defer so page CSS/JS and module APIs are not blocked on php artisan serve
                const scheduleNotifications = () => {
                    if (typeof requestIdleCallback === 'function') {
                        requestIdleCallback(() => loadNotifications(), { timeout: 4000 });
                    } else {
                        setTimeout(loadNotifications, 2500);
                    }
                };
                scheduleNotifications();
                setInterval(loadNotifications, 120000);

                const notifBell = document.getElementById('notificationBell');
                if (notifBell) {
                    notifBell.addEventListener('show.bs.dropdown', loadNotifications);
                }
            })();
        </script>

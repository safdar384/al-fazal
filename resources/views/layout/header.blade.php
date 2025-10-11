<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <title>{{ config('app.name', 'TradGate') }} - @yield('title', 'Dashboard')</title>
    
    <!--begin::Fonts-->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/source-sans-3.css') }}" />
    
    <!--begin::Bootstrap Icons-->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" />
    
    <!--begin::AdminLTE CSS-->
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.css') }}" />
    
    <!--begin::OverlayScrollbars-->
    <link rel="stylesheet" href="{{ asset('vendor/overlayscrollbars/overlayscrollbars.min.css') }}" />
    
    <!--begin::Select2 CSS-->
    <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}" />
    
    <!--begin::DataTables CSS-->
    <link rel="stylesheet" href="{{ asset('vendor/datatables/dataTables.bootstrap5.min.css') }}" />
    
    <!--begin::jQuery (Required for Select2 & DataTables)-->
    <script src="{{ asset('vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    
    <!--begin::Select2 JS-->
    <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
    
    <!--begin::DataTables JS-->
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
    
    <!--begin::SweetAlert2-->
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <!--begin::Page Specific Styles-->
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
        
        /* Sidebar Enhancements */
        .sidebar-brand {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 2px solid rgba(255,255,255,0.1);
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
            background: rgba(255,255,255,0.1) !important;
            border-left: 3px solid #667eea;
        }
        
        /* Submenu Styling */
        .nav-treeview .nav-link {
            padding-left: 2.5rem !important;
        }
        .nav-treeview .nav-link:hover {
            background: rgba(255,255,255,0.05);
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
        }
        
        /* Dropdown Enhancements */
        .dropdown-menu-lg {
            min-width: 350px;
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
        
        /* Smooth Transitions */
        .nav-link, .dropdown-item {
            transition: all 0.2s ease;
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <!--begin::Header-->
        <nav class="app-header navbar navbar-expand bg-body shadow-sm">
            <div class="container-fluid">
                <!--begin::Start Navbar Links-->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list fs-5"></i>
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
                <div class="d-none d-lg-flex flex-grow-1 justify-content-center px-3">
                    <form class="d-flex" role="search" style="max-width: 500px; width: 100%;">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input class="form-control border-start-0" type="search" placeholder="Search products, customers, invoices..." aria-label="Search">
                        </div>
                    </form>
                </div>
                <!--end::Center Search-->
                
                <!--begin::End Navbar Links-->
                <ul class="navbar-nav ms-auto">
                    
                    <!--begin::Quick Actions-->
                    <li class="nav-item dropdown d-none d-md-block">
                        <a class="nav-link" href="#" data-bs-toggle="dropdown" title="Quick Actions">
                            <i class="bi bi-plus-circle fs-5"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
                            <span class="dropdown-item dropdown-header fw-semibold">Quick Actions</span>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('product.create') }}" class="dropdown-item">
                                <i class="bi bi-box-seam me-2 text-primary"></i> New Product
                            </a>
                            <a href="{{ route('Customer.create') }}" class="dropdown-item">
                                <i class="bi bi-person-plus me-2 text-success"></i> New Customer
                            </a>
                            <a href="{{ route('NewAgent.create') }}" class="dropdown-item">
                                <i class="bi bi-person-badge me-2 text-info"></i> New Agent
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ url('/saleInvoice') }}" class="dropdown-item">
                                <i class="bi bi-receipt me-2 text-warning"></i> New Sale Invoice
                            </a>
                        </div>
                    </li>
                    <!--end::Quick Actions-->
                    
                    <!--begin::Notifications-->
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" data-bs-toggle="dropdown" title="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            <span class="badge badge-warning navbar-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
                            <span class="dropdown-item dropdown-header fw-semibold">3 Notifications</span>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small fw-semibold">Low Stock Alert</h6>
                                        <p class="mb-1 small text-muted">5 products below minimum quantity</p>
                                        <p class="mb-0 small text-muted">
                                            <i class="bi bi-clock"></i> 2 hours ago
                                        </p>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="bi bi-cash-coin text-success fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small fw-semibold">Payment Received</h6>
                                        <p class="mb-1 small text-muted">Customer payment of Rs. 25,000</p>
                                        <p class="mb-0 small text-muted">
                                            <i class="bi bi-clock"></i> 4 hours ago
                                        </p>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <div class="d-flex align-items-start">
                                    <div class="me-3">
                                        <i class="bi bi-receipt text-primary fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small fw-semibold">New Invoice</h6>
                                        <p class="mb-1 small text-muted">Invoice #INV-001 created</p>
                                        <p class="mb-0 small text-muted">
                                            <i class="bi bi-clock"></i> 6 hours ago
                                        </p>
                                    </div>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item dropdown-footer text-center">View All Notifications</a>
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
                                TG
                            </div>
                            <span class="d-none d-md-inline fw-semibold">TradeGate</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
                            <div class="dropdown-item dropdown-header text-center py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="rounded-circle shadow d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-2" 
                                     style="width: 80px; height: 80px; background: rgba(255,255,255,0.2);">
                                    <span style="font-size: 2rem;">TG</span>
                                </div>
                                <h6 class="mb-0 text-white fw-bold">TradeGate</h6>
                                <small class="text-white-50">Trade Management System</small>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ url('/dashboard') }}" class="dropdown-item">
                                <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-person me-2 text-info"></i> My Profile
                            </a>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-gear me-2 text-secondary"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item">
                                <i class="bi bi-question-circle me-2 text-warning"></i> Help & Support
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Sign Out
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

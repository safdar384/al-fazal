{{-- Fixed bottom shortcuts (mobile / tablet). Toggles use AdminLTE data-lte-toggle="sidebar" (bound on DOMContentLoaded). --}}
@php
    $erpNavActive = function (array $patterns): bool {
        foreach ($patterns as $p) {
            if ($p !== '' && request()->is($p)) {
                return true;
            }
        }
        return false;
    };
@endphp
<nav class="erp-mobile-nav d-lg-none" aria-label="Quick navigation">
    <a href="{{ url('/dashboard') }}" class="erp-mobile-nav__item {{ $erpNavActive(['dashboard']) ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>Home</span>
    </a>
    <a href="{{ url('/SaleInvoice') }}" class="erp-mobile-nav__item {{ $erpNavActive(['SaleInvoice', 'cash-sale', 'saleSummary']) ? 'active' : '' }}">
        <i class="bi bi-receipt"></i>
        <span>Sale</span>
    </a>
    <a href="{{ route('order.index') }}" class="erp-mobile-nav__item {{ $erpNavActive(['new-order', 'new-order/*', 'order-summary*', 'order-approval*']) ? 'active' : '' }}">
        <i class="bi bi-journal-check"></i>
        <span>Orders</span>
    </a>
    <button type="button" class="erp-mobile-nav__item" data-lte-toggle="sidebar" aria-label="Open menu">
        <i class="bi bi-list"></i>
        <span>Menu</span>
    </button>
</nav>

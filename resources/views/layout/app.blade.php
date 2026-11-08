@include('layout.header')

@include('layout.sidebar')

<div class="main-content flex-grow-1 min-w-0 px-2 px-sm-3 pb-3">
    @yield('content')
</div>

@include('layout.mobile-quick-nav')

{{-- Global JS --}}
<script src="{{ asset('JS/global.js') }}"></script>

@include('layout.footer')

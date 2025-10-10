@include('layout.header')

@include('layout.sidebar')

<div class="main-content">
    @yield('content')
</div>

{{-- Global Confirm Modal (only one instance in layout) --}}
@include('components.confirm-modal')

{{-- Global JS --}}
<script src="{{ asset('js/global.js') }}"></script>
@stack('scripts')

@include('layout.footer')

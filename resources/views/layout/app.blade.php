@include('layout.header')

@include('layout.sidebar')

<div class="main-content">
    @yield('content')
</div>

{{-- Global JS --}}
<script src="{{ asset('JS/global.js') }}"></script>

@include('layout.footer')

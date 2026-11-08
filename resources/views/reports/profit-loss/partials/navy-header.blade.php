{{-- Shared company logo/name only — report title stays below --}}
@include('components.invoiceHeader')
@isset($title)
<div class="report-title">
    <h3>{{ $title }}</h3>
</div>
@endisset

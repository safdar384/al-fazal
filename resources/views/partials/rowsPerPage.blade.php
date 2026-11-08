@php
    $rowsId = $rowsId ?? 'rowsPerPage';
    $rowsValue = (int) ($rowsValue ?? request('per_page', 25));
    if (! in_array($rowsValue, [10, 25, 50, 100], true)) {
        $rowsValue = 25;
    }
@endphp
<div class="{{ $rowsWrap ?? 'col-md-auto' }}">
    <label class="form-label small fw-semibold mb-1" for="{{ $rowsId }}">Rows</label>
    <select name="per_page" id="{{ $rowsId }}" class="form-select form-select-sm" style="width: 5.5rem;" aria-label="Rows per page">
        @foreach ([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" @selected($rowsValue === $size)>{{ $size }}</option>
        @endforeach
    </select>
</div>

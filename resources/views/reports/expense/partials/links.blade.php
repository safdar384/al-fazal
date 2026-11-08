@php
    $expenseLinks = [
        ['label' => 'Expense Report Summary', 'route' => 'reports.expense.summary', 'icon' => 'bi-cash-stack'],
        ['label' => 'Expenses by Category', 'route' => 'reports.expense.by-category', 'icon' => 'bi-tags'],
    ];
@endphp

<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-danger bg-opacity-10 border-0">
        <h6 class="mb-0 fw-semibold text-danger">Expense Section</h6>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            @foreach ($expenseLinks as $link)
                <a href="{{ route($link['route']) }}" target="_blank" rel="noopener" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi {{ $link['icon'] }} me-2"></i>{{ $link['label'] }}</span>
                    <i class="bi bi-arrow-right-short fs-5"></i>
                </a>
            @endforeach
        </div>
    </div>
</div>

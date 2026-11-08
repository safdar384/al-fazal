@extends('layout.app')
@section('title', 'Cost Report — '.$product->productName)

@push('styles')
<style>
  .erp-invoice-header-rule { border-top: 2px solid #0d6efd; }
  .cost-report-product-box {
    background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem 1.25rem;
  }
  .cost-report-total-row {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    font-size: 1.1rem;
    font-weight: 700;
  }
  @media print {
    @page { size: A4; margin: 8mm 10mm; }
    .app-sidebar, .app-header, .app-content-header, .no-print { display: none !important; }
    body, .app-main, .app-content, .container-fluid {
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
      font-size: 9pt !important;
      line-height: 1.25 !important;
    }
    .card {
      box-shadow: none !important;
      border: 1px solid #ccc !important;
      margin-bottom: 6px !important;
      break-inside: avoid;
    }
    .card-header {
      padding: 3px 6px !important;
      background: #f5f5f5 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .card-header h5, .card-header .fs-6 {
      font-size: 9pt !important;
      margin: 0 !important;
    }
    .card-header .bi { display: none !important; }
    .card-body { padding: 0 !important; }
    .cost-report-product-box {
      padding: 6px 8px !important;
      margin-bottom: 6px !important;
      background: #f8f9fa !important;
      border-radius: 0 !important;
    }
    .cost-report-product-box h4 {
      font-size: 11pt !important;
      margin-bottom: 2px !important;
    }
    .cost-report-product-box .small,
    .cost-report-product-box .text-muted {
      font-size: 8pt !important;
    }
    .company-header {
      padding-bottom: 4px !important;
      margin-bottom: 6px !important;
      border-bottom-width: 1px !important;
    }
    .company-name { font-size: 12pt !important; margin-bottom: 0 !important; }
    .company-tagline, .company-contact { font-size: 7pt !important; line-height: 1.2 !important; }
    .monogram-logo { font-size: 1.6rem !important; }
    .monogram-text { font-size: 6pt !important; }
    .company-monogram { flex: 0 0 70px !important; }
    table.table {
      font-size: 8pt !important;
      margin-bottom: 0 !important;
    }
    table.table th,
    table.table td {
      padding: 2px 4px !important;
      vertical-align: middle !important;
    }
    table.table thead th {
      font-size: 7.5pt !important;
      font-weight: 600 !important;
    }
    table.table tbody td small {
      font-size: 7pt !important;
      line-height: 1.1 !important;
    }
    table.table tbody td strong {
      font-weight: 600 !important;
      font-size: 8pt !important;
    }
    .cost-report-total-row td {
      font-size: 9pt !important;
      padding: 4px 6px !important;
      background: #333 !important;
      color: #fff !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .table-primary th, .table-success th {
      background: #e9ecef !important;
      color: #000 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
  }
</style>
@endpush

@section('content')
<main class="app-main erp-invoice-page" id="main">
  <div class="app-content-header">
    <div class="container-fluid px-2 px-md-3">
      <div class="row align-items-center gy-2">
        <div class="col-12 col-md-6">
          <h3 class="mb-0">
            <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Product Cost Report
          </h3>
        </div>
        <div class="col-12 col-md-6">
          <ol class="breadcrumb float-md-end mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('bom.index') }}">Product Cost</a></li>
            <li class="breadcrumb-item active">Cost Report</li>
          </ol>
        </div>
      </div>
      <hr class="erp-invoice-header-rule mb-3">
    </div>
  </div>

  <div class="app-content">
    <div class="container-fluid px-2 px-md-3" style="max-width: var(--erp-content-max, 1100px); margin: 0 auto;">

      @include('Components.invoiceHeader')

      <div class="cost-report-product-box mb-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-8">
            <h4 class="mb-1 fw-bold text-dark">{{ $product->productName }}</h4>
            <div class="text-muted small">
              <span class="me-3"><strong>Code:</strong> {{ $product->productCode }}</span>
              @if($product->companyName)
                <span><strong>Company:</strong> {{ $product->companyName }}</span>
              @endif
            </div>
          </div>
          <div class="col-md-4 text-md-end">
            <div class="small text-muted">{{ $costPerLabel }}</div>
            @if($effectiveFrom)
              <div class="small text-muted">Effective: {{ $effectiveFrom }}</div>
            @endif
            <div class="small text-muted">Report Date: {{ now()->format('d-M-Y') }}</div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header bg-light border-bottom p-2">
          <h5 class="mb-0 text-dark fs-6 fw-semibold">
            <i class="bi bi-box-seam me-2 text-primary"></i>Metrial
          </h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
              <thead class="table-primary">
                <tr>
                  <th style="width:40px">#</th>
                  <th>Metrial Name</th>
                  <th>Packing</th>
                  <th class="text-end">Rate</th>
                  <th class="text-end">Per Dozen</th>
                  <th class="text-end">Use Qty</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                @forelse($materialRows as $i => $row)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                      <strong>{{ $row['name'] }}</strong>
                      <br><small class="text-muted">{{ $row['code'] }}</small>
                    </td>
                    <td>{{ $row['packing'] }}</td>
                    <td class="text-end">{{ number_format($row['rate'], 2) }}</td>
                    <td class="text-end">{{ number_format($row['per_dozen'], 3) }}</td>
                    <td class="text-end">{{ number_format($row['use_qty'], 3) }}</td>
                    <td class="text-end fw-semibold">{{ number_format($row['total'], 2) }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-3">No metrial added</td>
                  </tr>
                @endforelse
              </tbody>
              @if(count($materialRows))
                <tfoot class="table-light">
                  <tr>
                    <th colspan="6" class="text-end">Material Amount</th>
                    <th class="text-end">{{ number_format($materialTotal, 2) }} {{ $currency }}</th>
                  </tr>
                </tfoot>
              @endif
            </table>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-3">
        <div class="card-header bg-light border-bottom p-2">
          <h5 class="mb-0 text-dark fs-6 fw-semibold">
            <i class="bi bi-people me-2 text-success"></i>Labour Work
          </h5>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
              <thead class="table-success">
                <tr>
                  <th style="width:40px">#</th>
                  <th>Work Name</th>
                  <th class="text-end">Qty</th>
                  <th class="text-end">Rate</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                @forelse($labourRows as $i => $row)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $row['name'] }}</strong></td>
                    <td class="text-end">{{ number_format($row['qty'], 3) }}</td>
                    <td class="text-end">{{ number_format($row['rate'], 2) }}</td>
                    <td class="text-end fw-semibold">{{ number_format($row['total'], 2) }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-3">No labour work added</td>
                  </tr>
                @endforelse
              </tbody>
              @if(count($labourRows))
                <tfoot class="table-light">
                  <tr>
                    <th colspan="4" class="text-end">Labour Work Amount</th>
                    <th class="text-end">{{ number_format($labourTotal, 2) }} {{ $currency }}</th>
                  </tr>
                </tfoot>
              @endif
            </table>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <tbody>
              <tr>
                <td class="text-end fw-semibold">Material Amount</td>
                <td class="text-end" style="width:180px">{{ number_format($materialTotal, 2) }} {{ $currency }}</td>
              </tr>
              <tr>
                <td class="text-end fw-semibold">Labour Work Amount</td>
                <td class="text-end">{{ number_format($labourTotal, 2) }} {{ $currency }}</td>
              </tr>
              <tr class="cost-report-total-row">
                <td class="text-end">{{ $costPerLabel }} — Total</td>
                <td class="text-end">{{ number_format($grandTotal, 2) }} {{ $currency }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 mb-4 no-print">
        <a href="{{ route('bom.index') }}" class="btn btn-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i>Back to Product Cost
        </a>
        <a href="{{ route('bom.builder', $product->productCode) }}" class="btn btn-warning btn-sm text-dark">
          <i class="bi bi-pencil-square me-1"></i>Edit Cost
        </a>
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
          <i class="bi bi-printer me-1"></i>Print
        </button>
      </div>

    </div>
  </div>
</main>
@endsection

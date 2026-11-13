<style>
    @page { size: A4 landscape; margin: 8mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        color: #1a1a1a;
        font-size: 9px;
        line-height: 1.3;
        background: #fff;
    }

    .pdf-banner-wrap {
        width: 100%;
        margin: 0 0 8px;
        border-bottom: 2px solid #0f2747;
        padding-bottom: 6px;
    }
    .pdf-banner {
        width: 100%;
        height: auto;
        display: block;
    }

    .pdf-header {
        width: 100%;
        border-bottom: 2px solid #0f2747;
        padding-bottom: 6px;
        margin-bottom: 8px;
    }
    .pdf-header table { width: 100%; border-collapse: collapse; }
    .pdf-brand {
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        color: #0f2747;
        letter-spacing: 0.4px;
    }
    .pdf-tag { font-size: 9px; font-weight: 700; color: #5a6a7a; margin-top: 2px; }
    .pdf-mark {
        border: 2px solid #0f2747;
        text-align: center;
        padding: 4px 8px;
        font-weight: 700;
        color: #0f2747;
        white-space: nowrap;
        border-radius: 3px;
    }

    .report-title {
        text-align: center;
        margin: 4px 0 8px;
    }
    .report-title h3 {
        font-size: 13px;
        font-weight: 700;
        margin: 0;
        color: #0f2747;
        letter-spacing: 0.8px;
        text-transform: uppercase;
    }

    .meta-bar {
        width: 100%;
        border-collapse: separate;
        border-spacing: 6px 0;
        margin: 0 0 8px;
    }
    .meta-bar td.meta-box {
        width: 50%;
        background: #f4f7fb;
        border: 1px solid #d5dde8;
        border-left: 3px solid #0f2747;
        border-radius: 3px;
        padding: 6px 8px;
        vertical-align: top;
    }
    .meta-box h6 {
        margin: 0 0 4px;
        font-size: 8px;
        font-weight: 700;
        color: #0f2747;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .meta-grid { width: 100%; border-collapse: collapse; }
    .meta-grid td {
        padding: 1px 0;
        font-size: 8.5px;
        vertical-align: top;
    }
    .meta-grid .lbl {
        width: 72px;
        color: #6b7c8d;
        font-weight: 700;
        white-space: nowrap;
    }
    .meta-grid .val {
        color: #1a1a1a;
        font-weight: 600;
    }

    .summary-stats {
        width: 100%;
        border-collapse: separate;
        border-spacing: 5px 0;
        margin: 0 0 8px;
    }
    .summary-stats td.stat-box {
        background: #f4f7fb;
        border: 1px solid #d5dde8;
        border-top: 3px solid #0f2747;
        text-align: center;
        padding: 6px 4px;
        vertical-align: middle;
    }
    .stat-label {
        display: block;
        font-size: 7px;
        font-weight: 700;
        color: #6b7c8d;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .stat-value {
        font-size: 10px;
        font-weight: 700;
        color: #0f2747;
    }

    .ledger-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 8px;
        margin-bottom: 8px;
    }
    .ledger-table thead th {
        background: #0f2747;
        color: #fff;
        padding: 5px 4px;
        font-size: 7.5px;
        font-weight: 700;
        text-align: center;
        border: 1px solid #0a1c33;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }
    .ledger-table tbody td {
        padding: 4px;
        border: 1px solid #cfd8e3;
        vertical-align: middle;
        color: #1a1a1a;
    }
    .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb; }
    .ledger-table .text-end { text-align: right; }
    .ledger-table .text-center { text-align: center; }
    .ledger-table .cell-desc { text-align: left; font-weight: 500; }
    .ledger-table .amt-credit { color: #198754; font-weight: 700; text-align: right; }
    .ledger-table .amt-debit { color: #dc3545; font-weight: 700; text-align: right; }
    .ledger-table .amt-balance { color: #0f2747; font-weight: 700; text-align: right; }
    .ledger-table tfoot th {
        background: #e8eef5;
        color: #0f2747;
        border: 1px solid #cfd8e3;
        padding: 5px 4px;
        font-size: 8px;
        font-weight: 700;
    }
    .empty-row td {
        text-align: center;
        color: #6b7c8d;
        padding: 14px 8px !important;
    }

    .summary-section {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #0f2747;
        margin-top: 2px;
    }
    .summary-section td.summary-item {
        width: 33.33%;
        text-align: center;
        padding: 8px 6px;
        border-right: 1px solid #d5dde8;
        vertical-align: middle;
    }
    .summary-section td.summary-item:last-child { border-right: none; }
    .summary-item small {
        display: block;
        color: #6b7c8d;
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 2px;
    }
    .summary-item .val {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        color: #0f2747;
    }
    .summary-item.credit .val { color: #198754; }
    .summary-item.debit .val { color: #dc3545; }
    .summary-item.balance .val { color: #0f2747; }

    .report-footer {
        margin-top: 8px;
        border-top: 1px solid #d5dde8;
        padding-top: 5px;
        color: #8a97a6;
        text-align: center;
        font-size: 7px;
    }

    /* Keep invoice PDF helpers available when this partial is used alone */
    .text-right { text-align: right; }
    .text-center { text-align: center; }
</style>

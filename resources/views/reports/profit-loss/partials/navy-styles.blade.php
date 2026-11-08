<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    @page { size: A4 landscape; margin: 8mm; }
    @media print {
        .no-print { display: none !important; }
        html, body {
            width: 297mm; margin: 0; padding: 0; background: #fff !important;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
        .ledger-container {
            box-shadow: none !important; border: none !important; border-radius: 0 !important;
            margin: 0; padding: 0; max-width: 100%;
        }
        .ledger-table thead th { background: #0f2747 !important; color: #fff !important; }
        .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb !important; }
        .meta-bar, .summary-stats, .stat-box, .filter-card, .pl-row {
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
    }
    body {
        background: #e8eef5;
        font-family: 'Segoe UI', 'Helvetica Neue', Tahoma, Geneva, Verdana, sans-serif;
        color: #1a1a1a; padding: 12px;
    }
    .ledger-container {
        width: 100%; max-width: 1120px; margin: 0 auto; background: #fff;
        box-shadow: 0 4px 24px rgba(15, 39, 71, 0.12); border-radius: 4px; padding: 14px 16px 16px;
    }
    .company-header {
        display: flex; justify-content: space-between; align-items: flex-start;
        border-bottom: 2px solid #0f2747; padding-bottom: 8px; margin-bottom: 8px;
    }
    .company-name { font-size: 1.55rem; font-weight: 700; color: #0f2747; letter-spacing: 0.4px; line-height: 1.1; }
    .company-tagline { color: #5a6a7a; font-size: 0.72rem; margin-top: 2px; }
    .company-contact { color: #6b7c8d; font-size: 0.68rem; margin-top: 1px; }
    .company-monogram {
        flex: 0 0 72px; text-align: center; border: 2px solid #0f2747; border-radius: 4px; padding: 4px 6px;
    }
    .monogram-logo { font-size: 1.75rem; font-weight: 800; color: #0f2747; line-height: 1; }
    .monogram-text { font-size: 0.52rem; color: #5a6a7a; font-weight: 700; letter-spacing: 1px; }
    .report-title { text-align: center; margin: 6px 0 8px; }
    .report-title h3 {
        font-size: 1.05rem; font-weight: 700; margin: 0; color: #0f2747;
        letter-spacing: 0.8px; text-transform: uppercase;
    }
    .meta-bar { display: grid; grid-template-columns: 1.2fr 1fr; gap: 8px; margin-bottom: 10px; }
    .meta-box {
        background: #f4f7fb; border: 1px solid #d5dde8; border-left: 3px solid #0f2747;
        border-radius: 3px; padding: 7px 10px;
    }
    .meta-box h6 {
        margin: 0 0 4px; font-size: 0.68rem; font-weight: 700; color: #0f2747;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .meta-grid { display: grid; grid-template-columns: auto 1fr; column-gap: 8px; row-gap: 2px; font-size: 0.72rem; }
    .meta-grid .lbl { color: #6b7c8d; font-weight: 600; }
    .meta-grid .val { color: #1a1a1a; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .filter-card {
        background: #f4f7fb; border: 1px solid #d5dde8; border-radius: 3px; padding: 8px 10px; margin-bottom: 10px;
    }
    .summary-stats { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
    .stat-box {
        flex: 1; min-width: 120px; text-align: center; padding: 8px 6px; background: #f4f7fb;
        border: 1px solid #d5dde8; border-radius: 3px;
    }
    .stat-box.value .stat-value { color: #198754; }
    .stat-box.warn .stat-value { color: #dc3545; }
    .stat-label {
        font-size: 0.62rem; color: #6b7c8d; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.4px; margin-bottom: 2px;
    }
    .stat-value { font-size: 0.92rem; font-weight: 700; color: #0f2747; }
    .ledger-table {
        width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 0.68rem; margin-bottom: 10px;
    }
    .ledger-table thead th {
        background: #0f2747; color: #fff; padding: 6px 5px; font-size: 0.62rem; font-weight: 700;
        text-align: center; border: 1px solid #0a1c33; vertical-align: middle; white-space: nowrap;
        text-transform: uppercase; letter-spacing: 0.3px;
    }
    .ledger-table tbody td {
        padding: 4px 5px; border: 1px solid #cfd8e3; vertical-align: middle; height: 26px;
        line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb; }
    .ledger-table tfoot td {
        padding: 5px; border: 1px solid #cfd8e3; background: #e8eef5; font-weight: 700; color: #0f2747;
    }
    .ledger-table .text-end { text-align: right !important; }
    .ledger-table .text-center { text-align: center !important; }
    .ledger-table .cell-desc { text-align: left !important; font-weight: 500; }
    .ledger-table .amt { color: #0f2747; font-weight: 700; }
    .ledger-table .amt-pos { color: #198754; font-weight: 700; }
    .ledger-table .amt-neg { color: #dc3545; font-weight: 700; }
    .pl-statement { border: 1px solid #d5dde8; border-radius: 3px; overflow: hidden; margin-bottom: 10px; }
    .pl-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 12px; border-bottom: 1px solid #eef1f5; font-size: 0.82rem;
    }
    .pl-row:last-child { border-bottom: none; }
    .pl-row.sub { background: #f4f7fb; font-weight: 700; }
    .pl-row.net { background: #0f2747; color: #fff; font-weight: 700; font-size: 0.95rem; }
    .pl-row .hint { display: block; font-size: 0.68rem; color: #6b7c8d; font-weight: 500; }
    .pl-row.net .hint { color: rgba(255,255,255,0.7); }
    .empty-row td {
        text-align: center; color: #6b7c8d; padding: 18px 8px !important; white-space: normal !important;
    }
    .report-footer {
        margin-top: 8px; border-top: 1px solid #d5dde8; padding-top: 6px;
        color: #8a97a6; text-align: center; font-size: 0.65rem;
    }
    .btn-print, .btn-back, .btn-pdf {
        position: fixed; bottom: 24px; padding: 9px 18px; font-size: 0.85rem;
        box-shadow: 0 4px 14px rgba(0,0,0,0.18); z-index: 1000; border-radius: 4px;
    }
    .btn-back { left: 24px; }
    .btn-pdf { right: 140px; }
    .btn-print { right: 24px; background: #0f2747; border-color: #0f2747; }
    .btn-print:hover { background: #1a4a8a; border-color: #1a4a8a; }
    .is-loading { opacity: 0.45; pointer-events: none; }
    .flash-ok {
        background: #e8f5e9; border: 1px solid #b7e4c7; color: #198754;
        border-radius: 3px; padding: 8px 10px; margin-bottom: 10px; font-size: 0.8rem;
    }
</style>

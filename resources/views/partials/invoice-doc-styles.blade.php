{{-- Shared neat invoice document styles (sale / purchase / returns / order) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --ink: #111827;
        --muted: #4b5563;
        --line: #e5e7eb;
        --navy: #0f2747;
        --soft: #f8fafc;
        --paper: #ffffff;
        --blue-dark: #061528;
        --blue-mid: #0a2340;
        --blue-label: #071a33;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        min-height: 100vh;
        padding: 1.25rem;
        color: var(--ink);
        font-family: Manrope, Arial, Helvetica, sans-serif;
        background: #edf1f5;
        -webkit-font-smoothing: antialiased;
    }

    .invoice-container {
        width: min(96vw, 72rem);
        margin: 0 auto;
        background: var(--paper);
        border: 1px solid #dbe2ea;
        box-shadow: 0 10px 28px rgba(15, 39, 71, 0.08);
        padding: 1.35rem 1.5rem 1.25rem;
        font-size: 0.98rem;
        line-height: 1.4;
        position: relative;
    }

    .invoice-container .company-header {
        display: block;
        margin-bottom: 0.85rem;
        padding-bottom: 0.65rem;
        border-bottom: 2px solid var(--navy);
    }

    .invoice-container .company-header-banner {
        width: 100%;
        height: auto;
        display: block;
    }

    .bill-row {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1.5rem;
        align-items: start;
        margin-bottom: 1rem;
        padding: 0.75rem 0;
        border-top: 1px solid var(--line);
        border-bottom: 1px solid var(--line);
    }

    .inv-side {
        text-align: right;
        justify-self: end;
        min-width: 12.5rem;
    }

    .inv-title {
        margin: 0 0 0.35rem;
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        color: var(--blue-dark);
        line-height: 1.1;
    }

    .inv-type {
        margin: 0 0 0.45rem;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--blue-mid);
    }

    .inv-meta div {
        margin-bottom: 0.2rem;
        font-size: 0.98rem;
        font-weight: 650;
        color: var(--blue-dark);
    }

    .inv-meta .k {
        color: var(--blue-mid);
        font-weight: 700;
        margin-right: 0.4rem;
    }

    .inv-meta .v {
        font-weight: 800;
        color: var(--blue-dark);
    }

    .sec-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--blue-label);
        margin-bottom: 0.35rem;
    }

    .bill-name {
        font-size: 1.02rem;
        font-weight: 800;
        color: var(--blue-dark);
        margin-bottom: 0.15rem;
    }

    .bill-line {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--blue-mid);
        line-height: 1.45;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0.85rem;
        font-size: 0.9rem;
    }

    .items-table thead th {
        background: var(--navy);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 0.5rem 0.45rem;
        text-align: left;
        border: none;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .items-table td {
        padding: 0.4rem 0.45rem;
        border-bottom: 1px solid var(--line);
        font-weight: 650;
        color: var(--ink);
        vertical-align: middle;
        white-space: nowrap;
        background: #fff;
    }

    .items-table tbody tr:nth-child(even) td { background: var(--soft); }
    .items-table td.product-name { white-space: nowrap; }

    .product-name-line {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 0.75rem;
    }

    .pn-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 750;
    }

    .pn-company {
        flex: 0 0 auto;
        margin-left: auto;
        color: var(--muted);
        font-weight: 650;
        text-align: right;
    }

    .text-right { text-align: right !important; }
    .text-center { text-align: center !important; }
    .text-end { text-align: right !important; }

    .cond-good { color: #198754; font-weight: 800; }
    .cond-damage { color: #dc3545; font-weight: 800; }

    .bottom-row {
        display: grid;
        grid-template-columns: 1fr minmax(14rem, 16rem);
        gap: 1rem;
        align-items: start;
    }

    .notes-box {
        border: 1px solid var(--line);
        background: var(--soft);
        padding: 0.65rem 0.8rem;
        min-height: 4.5rem;
    }

    .notes-box h6 {
        margin: 0 0 0.25rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--navy);
    }

    .notes-box p,
    .notes-box .bill-line {
        margin: 0.12rem 0 0;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--ink);
    }

    .notes-box .muted {
        color: var(--muted);
        font-weight: 600;
    }

    .transport-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.25rem 0.75rem;
    }

    .transport-grid p {
        margin: 0;
        font-size: 0.86rem;
    }

    .transport-grid span {
        display: block;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .transport-grid strong {
        font-weight: 750;
        color: var(--ink);
    }

    .totals-box {
        border: 1px solid var(--line);
        overflow: hidden;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.4rem 0.7rem;
        border-bottom: 1px solid var(--line);
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--ink);
        background: #fff;
    }

    .total-row:last-child { border-bottom: none; }

    .total-row.grand-total,
    .total-row.grand {
        background: var(--navy);
        color: #fff;
        font-size: 1.05rem;
        font-weight: 800;
        padding: 0.55rem 0.7rem;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .total-row.grand-total span,
    .total-row.grand-total strong,
    .total-row.grand span,
    .total-row.grand strong {
        color: #fff;
    }

    .signature-section {
        display: flex;
        justify-content: space-between;
        gap: 2rem;
        margin-top: 1.75rem;
        padding-top: 0.5rem;
    }

    .signature-box {
        flex: 1;
        max-width: 14rem;
        text-align: center;
    }

    .signature-line {
        border-top: 1.5px solid var(--ink);
        margin-top: 2.2rem;
        padding-top: 0.35rem;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--ink);
    }

    .thanks-line {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.8rem;
        font-weight: 650;
        color: var(--muted);
    }

    .btn-print,
    .btn-back,
    .btn-pdf,
    .btn-whatsapp,
    .btn-convert {
        position: fixed;
        bottom: 28px;
        padding: 10px 16px;
        font-size: 0.85rem;
        z-index: 1000;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.18);
    }
    .btn-print { right: 28px; }
    .btn-pdf {
        right: 125px;
        background: #b91c1c;
        border-color: #b91c1c;
        color: #fff;
    }
    .btn-pdf:hover { background: #991b1b; border-color: #991b1b; color: #fff; }
    .btn-whatsapp {
        right: 235px;
        background: #25D366;
        border-color: #25D366;
        color: #fff;
    }
    .btn-whatsapp:hover { background: #1ebe57; border-color: #1ebe57; color: #fff; }
    .btn-convert {
        right: 235px;
        background: #198754;
        border-color: #198754;
        color: #fff;
    }
    .btn-back { left: 28px; }

    .status-watermark {
        position: absolute;
        top: 48%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-28deg);
        font-size: 4.5rem;
        font-weight: 900;
        letter-spacing: 0.12em;
        color: rgba(15, 39, 71, 0.06);
        pointer-events: none;
        z-index: 0;
        white-space: nowrap;
    }

    @media (max-width: 720px) {
        .bill-row,
        .bottom-row { grid-template-columns: 1fr; }
        .inv-side { text-align: left; justify-self: start; }
        .transport-grid { grid-template-columns: 1fr; }
        .btn-pdf { right: 28px; bottom: 82px; }
        .btn-whatsapp,
        .btn-convert { right: 28px; bottom: 136px; }
    }

    @media print {
        .no-print { display: none !important; }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
            color: #000 !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        .invoice-container {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            font-size: 10.5pt !important;
            color: #000 !important;
            background: #fff !important;
        }

        .invoice-container,
        .invoice-container * {
            color: #000 !important;
            text-shadow: none !important;
            box-shadow: none !important;
            opacity: 1 !important;
        }

        .company-header { border-bottom: 2pt solid #000 !important; }

        .inv-title {
            font-size: 16pt !important;
            font-weight: 900 !important;
        }

        .inv-meta div {
            font-size: 10.5pt !important;
            font-weight: 700 !important;
        }

        .bill-row {
            border-top: 1pt solid #000 !important;
            border-bottom: 1pt solid #000 !important;
        }

        .items-table thead th {
            background: #fff !important;
            color: #000 !important;
            border-top: 1.5pt solid #000 !important;
            border-bottom: 2pt solid #000 !important;
            font-weight: 900 !important;
        }

        .items-table td,
        .items-table tbody tr:nth-child(even) td {
            background: #fff !important;
            border-bottom: 1pt solid #000 !important;
            font-weight: 700 !important;
            font-size: 10pt !important;
            padding: 3px 4px !important;
        }

        .notes-box,
        .totals-box {
            background: #fff !important;
            border: 1.5pt solid #000 !important;
        }

        .total-row {
            background: #fff !important;
            border-bottom: 1pt solid #000 !important;
        }

        .total-row.grand-total,
        .total-row.grand,
        .total-row.grand-total span,
        .total-row.grand-total strong,
        .total-row.grand span,
        .total-row.grand strong {
            background: #fff !important;
            color: #000 !important;
            font-weight: 900 !important;
            font-size: 12pt !important;
            border-top: 2pt solid #000 !important;
            border-bottom: 2.25pt double #000 !important;
        }

        .signature-section { margin-top: 12mm; }
        .signature-line {
            margin-top: 14mm;
            border-top: 1.5pt solid #000 !important;
        }

        .status-watermark { display: none !important; }

        @page {
            size: A4;
            margin: 8mm;
        }
    }
</style>

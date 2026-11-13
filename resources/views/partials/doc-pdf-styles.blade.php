<style>
    @page { size: A4; margin: 8mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        color: #000;
        font-size: 10.5px;
        line-height: 1.35;
        background: #fff;
    }
    .pdf-banner-wrap {
        width: 100%;
        margin: 0 0 10px;
        border-bottom: 2px solid #000;
        padding-bottom: 6px;
    }
    .pdf-banner {
        width: 100%;
        height: auto;
        display: block;
    }
    .pdf-header {
        width: 100%;
        border-bottom: 2px solid #000;
        padding-bottom: 6px;
        margin-bottom: 10px;
    }
    .pdf-header table { width: 100%; border-collapse: collapse; }
    .pdf-brand { font-size: 16px; font-weight: 700; text-transform: uppercase; color: #000; }
    .pdf-tag { font-size: 10px; font-weight: 700; color: #000; margin-top: 2px; }
    .pdf-mark {
        border: 2px solid #000;
        text-align: center;
        padding: 4px 8px;
        font-weight: 700;
        color: #000;
        white-space: nowrap;
    }
    .pdf-title {
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #000;
        margin: 0 0 2px;
    }
    .pdf-type { font-size: 9px; font-weight: 700; text-transform: uppercase; color: #000; margin-bottom: 4px; }
    .pdf-meta { font-size: 10px; color: #000; }
    .pdf-meta .k { font-weight: 700; margin-right: 4px; }
    .sec-label { font-size: 8px; font-weight: 700; text-transform: uppercase; color: #000; margin-bottom: 2px; }
    .bill-name { font-size: 12px; font-weight: 700; color: #000; }
    .bill-line { font-size: 10px; color: #000; margin-top: 1px; }
    .bill-table { width: 100%; border-collapse: collapse; margin: 8px 0 10px; }
    .bill-table td { vertical-align: top; color: #000; }
    .bill-table .right { text-align: right; width: 38%; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
    table.items th, table.items td {
        border: 1px solid #000;
        padding: 3px 4px;
        color: #000;
        vertical-align: top;
    }
    table.items th {
        background: #fff;
        font-size: 9px;
        text-transform: uppercase;
        text-align: left;
        font-weight: 700;
    }
    table.items .text-right { text-align: right; }
    table.items .text-center { text-align: center; }
    .bottom-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .bottom-table td { vertical-align: top; color: #000; }
    .notes-box, .totals-box {
        border: 1.5px solid #000;
        padding: 6px 8px;
        color: #000;
    }
    .notes-box { width: 58%; }
    .totals-box { width: 40%; }
    .notes-box h6 { font-size: 9px; text-transform: uppercase; margin-bottom: 4px; color: #000; }
    .notes-box p { font-size: 10px; margin: 2px 0; color: #000; }
    .total-row { width: 100%; border-collapse: collapse; }
    .total-row td { border: none; padding: 2px 0; color: #000; font-size: 10px; }
    .total-row .lbl { text-align: left; }
    .total-row .amt { text-align: right; font-weight: 700; }
    .total-row.grand td {
        border-top: 2px solid #000;
        border-bottom: 2.5px double #000;
        padding-top: 4px;
        font-size: 12px;
        font-weight: 700;
    }
    .sig-table { width: 100%; margin-top: 22px; border-collapse: collapse; }
    .sig-table td { width: 50%; padding-top: 18px; color: #000; }
    .sig-line {
        border-top: 1.5px solid #000;
        width: 70%;
        padding-top: 4px;
        font-size: 9px;
        text-align: center;
    }
    .thanks { text-align: center; margin-top: 12px; font-size: 9px; color: #000; }
    .report-h1 { font-size: 14px; font-weight: 700; color: #000; margin: 0 0 4px; }
    .report-meta { font-size: 10px; color: #000; margin-bottom: 8px; }
    table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
    table.data th, table.data td {
        border: 1px solid #000;
        padding: 3px 4px;
        color: #000;
        vertical-align: top;
    }
    table.data th { background: #fff; font-weight: 700; text-align: left; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
</style>

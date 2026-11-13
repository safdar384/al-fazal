<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        @media print {
            .no-print { display: none !important; }
            html, body {
                width: 297mm;
                margin: 0;
                padding: 0;
                background: #fff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .ledger-container {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .ledger-table thead th {
                background: #0f2747 !important;
                color: #fff !important;
            }
            .ledger-table tbody tr:nth-child(even) td {
                background: #f4f7fb !important;
            }
            .ledger-table tfoot td {
                background: #e8eef5 !important;
            }
            .meta-bar, .summary-stats, .stat-box, .status-pill {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        body {
            background: #e8eef5;
            font-family: 'Segoe UI', 'Helvetica Neue', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a1a1a;
            padding: 12px;
        }
        .ledger-container {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 4px 24px rgba(15, 39, 71, 0.12);
            border-radius: 4px;
            padding: 14px 16px 16px;
        }
        .company-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f2747;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 1.55rem;
            font-weight: 700;
            color: #0f2747;
            letter-spacing: 0.4px;
            line-height: 1.1;
        }
        .company-tagline { color: #5a6a7a; font-size: 0.72rem; margin-top: 2px; }
        .company-contact { color: #6b7c8d; font-size: 0.68rem; margin-top: 1px; }
        .company-monogram {
            flex: 0 0 72px;
            text-align: center;
            border: 2px solid #0f2747;
            border-radius: 4px;
            padding: 4px 6px;
        }
        .monogram-logo { font-size: 1.75rem; font-weight: 800; color: #0f2747; line-height: 1; }
        .monogram-text { font-size: 0.52rem; color: #5a6a7a; font-weight: 700; letter-spacing: 1px; }
        .report-title { text-align: center; margin: 6px 0 8px; }
        .report-title h3 {
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
            color: #0f2747;
            letter-spacing: 0.8px;
        }
        .meta-bar {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }
        .meta-box {
            background: #f4f7fb;
            border: 1px solid #d5dde8;
            border-left: 3px solid #0f2747;
            border-radius: 3px;
            padding: 7px 10px;
        }
        .meta-box h6 {
            margin: 0 0 4px;
            font-size: 0.68rem;
            font-weight: 700;
            color: #0f2747;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            column-gap: 8px;
            row-gap: 2px;
            font-size: 0.72rem;
        }
        .meta-grid .lbl { color: #6b7c8d; font-weight: 600; }
        .meta-grid .val {
            color: #1a1a1a;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }
        .stat-box {
            text-align: left;
            padding: 8px 10px;
            background: #fff;
            border: 1px solid #d5dde8;
            border-left: 4px solid #6c757d;
            border-radius: 3px;
        }
        .stat-box.st-debit { border-left-color: #dc3545; background: #fdf0f1; }
        .stat-box.st-credit { border-left-color: #198754; background: #eefaf2; }
        .stat-box.st-balance { border-left-color: #0f2747; background: #f4f7fb; }
        .stat-box.st-count { border-left-color: #1a4a8a; background: #f4f7fb; }
        .stat-label {
            font-size: 0.6rem;
            color: #6b7c8d;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }
        .stat-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: #0f2747;
        }
        .stat-box.st-debit .stat-value { color: #dc3545; }
        .stat-box.st-credit .stat-value { color: #198754; }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 0.68rem;
            margin-bottom: 10px;
        }
        .ledger-table thead th {
            background: #0f2747;
            color: #fff;
            padding: 6px 5px;
            font-size: 0.62rem;
            font-weight: 700;
            text-align: center;
            border: 1px solid #0a1c33;
            vertical-align: middle;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .ledger-table tbody td,
        .ledger-table tfoot td {
            padding: 4px 5px;
            border: 1px solid #cfd8e3;
            vertical-align: middle;
            height: 26px;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ledger-table tbody tr:nth-child(even) td { background: #f4f7fb; }
        .ledger-table tfoot td {
            background: #e8eef5;
            font-weight: 700;
            color: #0f2747;
        }
        .ledger-table .text-end { text-align: right !important; }
        .ledger-table .text-center { text-align: center !important; }
        .ledger-table .cell-desc { text-align: left !important; font-weight: 500; }
        .ledger-table .amt-debit { color: #dc3545; font-weight: 700; }
        .ledger-table .amt-credit { color: #198754; font-weight: 700; }
        .ledger-table .amt-bal { color: #0f2747; font-weight: 700; }
        .status-pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 0.58rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .st-debit { background: #fde8ea; color: #842029; }
        .st-credit { background: #e8f5e9; color: #198754; }
        .empty-row td {
            text-align: center;
            color: #6b7c8d;
            padding: 18px 8px !important;
            white-space: normal !important;
        }
        .signatures {
            display: flex;
            justify-content: space-around;
            margin-top: 18px;
            margin-bottom: 8px;
        }
        .sig-box {
            width: 160px;
            text-align: center;
        }
        .sig-line {
            border-bottom: 1px solid #0f2747;
            height: 36px;
            margin-bottom: 4px;
        }
        .sig-label {
            font-size: 0.62rem;
            font-weight: 700;
            color: #5a6a7a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .report-footer {
            margin-top: 8px;
            border-top: 1px solid #d5dde8;
            padding-top: 6px;
            color: #8a97a6;
            text-align: center;
            font-size: 0.65rem;
        }
        .btn-print, .btn-back, .btn-pdf {
            position: fixed;
            bottom: 24px;
            padding: 9px 18px;
            font-size: 0.85rem;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
            z-index: 1000;
            border-radius: 4px;
        }
        .btn-back { left: 24px; }
        .btn-pdf { right: 140px; }
        .btn-print { right: 24px; background: #0f2747; border-color: #0f2747; }
        .btn-print:hover { background: #1a4a8a; border-color: #1a4a8a; }
    </style>
</head>
<body>
    <div class="ledger-container">
        @include('components.invoiceHeader')

        <div class="report-title">
            <h3>EXPENSE LEDGER REPORT</h3>
        </div>

        <div class="meta-bar">
            <div class="meta-box">
                <h6>Filters</h6>
                <div class="meta-grid">
                    <span class="lbl">Period</span>
                    <span class="val" id="reportPeriod">All Time</span>
                    <span class="lbl">Expense</span>
                    <span class="val" id="filterExpense">All</span>
                    <span class="lbl">Category</span>
                    <span class="val" id="filterCategory">All</span>
                </div>
            </div>
            <div class="meta-box">
                <h6>Report</h6>
                <div class="meta-grid">
                    <span class="lbl">Generated</span>
                    <span class="val">{{ now()->format('d-M-Y h:i A') }}</span>
                    <span class="lbl">Records</span>
                    <span class="val" id="metaRecords">0</span>
                </div>
            </div>
        </div>

        <div class="summary-stats">
            <div class="stat-box st-debit">
                <div class="stat-label">Total Debits</div>
                <div class="stat-value" id="summaryDebit">0.00</div>
            </div>
            <div class="stat-box st-credit">
                <div class="stat-label">Total Credits</div>
                <div class="stat-value" id="summaryCredit">0.00</div>
            </div>
            <div class="stat-box st-balance">
                <div class="stat-label">Net Balance</div>
                <div class="stat-value" id="summaryBalance">0.00</div>
            </div>
            <div class="stat-box st-count">
                <div class="stat-label">Transactions</div>
                <div class="stat-value" id="summaryCount">0</div>
            </div>
        </div>

        <table class="ledger-table">
            <thead>
                <tr>
                    <th style="width:4%;">#</th>
                    <th style="width:10%;">Date</th>
                    <th style="width:16%;">Expense Name</th>
                    <th style="width:20%;">Description</th>
                    <th style="width:9%;">Category</th>
                    <th style="width:11%;">Amount</th>
                    <th style="width:10%;">Debit</th>
                    <th style="width:10%;">Credit</th>
                    <th style="width:10%;">Balance</th>
                </tr>
            </thead>
            <tbody id="reportTableBody">
                <tr class="empty-row">
                    <td colspan="9">Loading expense data...</td>
                </tr>
            </tbody>
            <tfoot id="reportTableFoot" style="display:none;">
                <tr>
                    <td colspan="5" class="text-end">TOTAL</td>
                    <td class="text-end" id="footAmount">0.00</td>
                    <td class="text-end" id="footDebit">0.00</td>
                    <td class="text-end" id="footCredit">0.00</td>
                    <td class="text-end" id="footBalance">0.00</td>
                </tr>
            </tfoot>
        </table>

        <div class="signatures">
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Prepared By</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Verified By</div>
            </div>
            <div class="sig-box">
                <div class="sig-line"></div>
                <div class="sig-label">Approved By</div>
            </div>
        </div>

        <div class="report-footer">
            This is a computer-generated report. &copy; {{ date('Y') }} Trade Gate
        </div>
    </div>

    <a href="{{ route('expense.ledger') }}" class="btn btn-secondary btn-back no-print">
        <i class="bi bi-arrow-left"></i> Back
    </a>
    <a href="{{ request()->fullUrlWithQuery(['download_pdf' => 1]) }}" class="btn btn-danger btn-pdf no-print">
        <i class="bi bi-file-earmark-pdf"></i> PDF
    </a>
    <button type="button" onclick="window.print()" class="btn btn-primary btn-print no-print">
        <i class="bi bi-printer"></i> Print Report
    </button>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            function fmt(n) {
                return parseFloat(n || 0).toLocaleString('en-PK', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function fmtDate(d) {
                if (!d) return '';
                try {
                    const dt = new Date(d);
                    if (isNaN(dt.getTime())) return d;
                    return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                } catch (e) {
                    return d;
                }
            }

            const params = new URLSearchParams(window.location.search);
            const expenseCode = params.get('expenseCode');
            const fromDate = params.get('fromDate');
            const toDate = params.get('toDate');
            const category = params.get('category');

            if (fromDate || toDate) {
                const fromLabel = fromDate ? fmtDate(fromDate) : 'Start';
                const toLabel = toDate ? fmtDate(toDate) : 'End';
                $('#reportPeriod').text(fromLabel + ' to ' + toLabel);
            } else {
                $('#reportPeriod').text('All Time');
            }
            $('#filterExpense').text(expenseCode || 'All');
            $('#filterCategory').text(category || 'All');

            function loadExpenseData() {
                const queryParams = new URLSearchParams();
                if (expenseCode) queryParams.append('expenseCode', expenseCode);
                if (fromDate) queryParams.append('fromDate', fromDate);
                if (toDate) queryParams.append('toDate', toDate);
                if (category) queryParams.append('category', category);

                $.get(`{{ url('/expense/ledger/report') }}?${queryParams}`)
                    .done(function (response) {
                        displayReport(response.data || []);
                    })
                    .fail(function () {
                        $('#reportTableBody').html('<tr class="empty-row"><td colspan="9">Failed to load expense data</td></tr>');
                        $('#reportTableFoot').hide();
                    });
            }

            function displayReport(data) {
                if (!data || data.length === 0) {
                    $('#reportTableBody').html('<tr class="empty-row"><td colspan="9">No transactions found for the selected filters.</td></tr>');
                    $('#summaryDebit, #summaryCredit, #summaryBalance, #footAmount, #footDebit, #footCredit, #footBalance').text('0.00');
                    $('#summaryCount, #metaRecords').text('0');
                    $('#reportTableFoot').hide();
                    return;
                }

                data.sort((a, b) => new Date(b.entryDate) - new Date(a.entryDate));

                let totalDebit = 0;
                let totalCredit = 0;
                let totalAmount = 0;
                let runningBalance = 0;

                data.forEach(row => {
                    totalDebit += parseFloat(row.debit || 0);
                    totalCredit += parseFloat(row.credit || 0);
                    totalAmount += parseFloat(row.amount || 0);
                });

                const reversedData = [...data].reverse();
                reversedData.forEach(row => {
                    runningBalance += parseFloat(row.debit || 0) - parseFloat(row.credit || 0);
                    row.runningBalance = runningBalance;
                });

                let html = '';
                data.forEach((row, idx) => {
                    const pill = row.category === 'Debit'
                        ? '<span class="status-pill st-debit">Debit</span>'
                        : '<span class="status-pill st-credit">Credit</span>';

                    const expenseName = row.expenseName || row.expenseCode || '—';
                    html += `
                        <tr>
                            <td class="text-center">${idx + 1}</td>
                            <td class="text-center">${fmtDate(row.entryDate)}</td>
                            <td class="cell-desc" title="${row.expenseCode || ''}">${expenseName}</td>
                            <td class="cell-desc" title="${row.description || ''}">${row.description || '—'}</td>
                            <td class="text-center">${pill}</td>
                            <td class="text-end">${fmt(row.amount)}</td>
                            <td class="text-end amt-debit">${fmt(row.debit)}</td>
                            <td class="text-end amt-credit">${fmt(row.credit)}</td>
                            <td class="text-end amt-bal">${fmt(row.runningBalance)}</td>
                        </tr>
                    `;
                });

                $('#reportTableBody').html(html);

                const netBalance = totalDebit - totalCredit;
                $('#summaryDebit').text(fmt(totalDebit));
                $('#summaryCredit').text(fmt(totalCredit));
                $('#summaryBalance').text(fmt(netBalance));
                $('#summaryCount').text(data.length);
                $('#metaRecords').text(data.length);

                $('#footAmount').text(fmt(totalAmount));
                $('#footDebit').text(fmt(totalDebit));
                $('#footCredit').text(fmt(totalCredit));
                $('#footBalance').text(fmt(netBalance));
                $('#reportTableFoot').show();

                // Resolve expense name for filter label if possible
                if (expenseCode && data[0] && data[0].expenseCode) {
                    $.get(`{{ url('/expense') }}/${encodeURIComponent(expenseCode)}/details`)
                        .done(function (res) {
                            if (res.expenseName) {
                                $('#filterExpense').text(expenseCode + ' — ' + res.expenseName);
                            }
                        });
                }
            }

            loadExpenseData();
        });
    </script>
</body>
</html>

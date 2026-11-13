{{-- Al Fazal letterhead: colorful banner on screen, solid black text on B&W laser print --}}
<style>
    .company-header {
        display: block !important;
        border-bottom: none !important;
        padding-bottom: 0;
        margin-bottom: 10px;
        gap: 0 !important;
    }

    .company-header-banner {
        display: block !important;
        width: 100%;
        max-width: 100%;
        height: auto;
        object-fit: contain;
        object-position: center top;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Print-safe text letterhead (HP LaserJet B&W) */
    .company-header-print {
        display: none;
        width: 100%;
        color: #000;
        border-bottom: 2.5pt solid #000;
        padding-bottom: 3mm;
        margin-bottom: 3mm;
        font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
    }

    .company-header-print .chp-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8mm;
    }

    .company-header-print .chp-brand {
        min-width: 0;
        flex: 1 1 auto;
    }

    .company-header-print .chp-name {
        margin: 0;
        font-size: 18pt;
        font-weight: 900;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: #000 !important;
        line-height: 1.15;
    }

    .company-header-print .chp-tag {
        margin: 1mm 0 0;
        font-size: 10pt;
        font-weight: 700;
        color: #000 !important;
        line-height: 1.25;
    }

    .company-header-print .chp-mark {
        flex: 0 0 auto;
        text-align: center;
        border: 2pt solid #000;
        padding: 1.5mm 3mm;
        min-width: 22mm;
    }

    .company-header-print .chp-mark-af {
        display: block;
        font-size: 16pt;
        font-weight: 900;
        letter-spacing: 0.08em;
        color: #000 !important;
        line-height: 1;
    }

    .company-header-print .chp-mark-sub {
        display: block;
        margin-top: 1mm;
        font-size: 7pt;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #000 !important;
        line-height: 1.1;
    }

    .company-info,
    .company-monogram {
        display: none !important;
    }

    @media print {
        .company-header-banner {
            display: none !important;
        }

        .company-header-print {
            display: block !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .company-header {
            margin-bottom: 4mm;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .company-header-print,
        .company-header-print * {
            color: #000 !important;
            text-shadow: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }
    }
</style>

<div class="company-header">
    <img
        src="{{ asset('images/al-fazal-header.jpg') }}"
        alt="Al Fazal Traders"
        class="company-header-banner"
    >

    <div class="company-header-print" aria-hidden="true">
        <div class="chp-top">
            <div class="chp-brand">
                <p class="chp-name">Al Fazal Traders</p>
                <p class="chp-tag">Azam Market Sargodha</p>
                <p class="chp-tag">Contact: 0322-6010149</p>
            </div>
            <div class="chp-mark">
                <span class="chp-mark-af">AF</span>
                <span class="chp-mark-sub">Al Fazal</span>
            </div>
        </div>
    </div>
</div>

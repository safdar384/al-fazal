<!-- Professional Invoice Header Component — Al Fazal letterhead -->
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
    }

    /* Keep legacy text classes harmless if referenced elsewhere */
    .company-info,
    .company-monogram {
        display: none !important;
    }

    @media print {
        .company-header {
            margin-bottom: 6px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .company-header-banner {
            max-height: 32mm;
            width: 100%;
        }
    }
</style>

<div class="company-header">
    <img
        src="{{ asset('images/al-fazal-header.jpg') }}"
        alt="Al Fazal"
        class="company-header-banner"
    >
</div>

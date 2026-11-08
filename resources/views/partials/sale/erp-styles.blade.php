<style>
.erp-sale-page .erp-sale-card,
.erp-invoice-page .si-card {
    border: 1px solid #d5dde8;
    box-shadow: 0 2px 12px rgba(15, 39, 71, 0.08);
    border-radius: 4px;
}
.erp-sale-page .erp-sale-card .card-header,
.erp-invoice-page .si-card-head {
    background: linear-gradient(135deg, #0f2747 0%, #1a4a8a 100%);
    color: #fff;
    border: 0;
}
.erp-sale-page .table-responsive,
.erp-invoice-page .table-responsive {
    -webkit-overflow-scrolling: touch;
}
.erp-sale-page #productTable,
.erp-invoice-page #productTable {
    min-width: 720px;
}
.erp-sale-page .erp-totals-panel {
    background: #f4f7fb;
    border: 1px solid #d5dde8;
    border-radius: 4px;
}
.erp-sale-sticky-actions,
.erp-invoice-sticky-actions {
    display: none;
}
@media (max-width: 991.98px) {
    .erp-sale-sticky-actions,
    .erp-invoice-sticky-actions {
        display: flex;
        gap: 0.5rem;
        position: fixed;
        left: 0;
        right: 0;
        bottom: calc(3.75rem + env(safe-area-inset-bottom, 0px));
        z-index: 1030;
        padding: 0.5rem 0.75rem;
        background: #fff;
        border-top: 1px solid #d5dde8;
        box-shadow: 0 -4px 16px rgba(15, 39, 71, 0.12);
    }
    .erp-sale-sticky-actions .btn,
    .erp-invoice-sticky-actions .btn {
        flex: 1;
        font-weight: 600;
    }
}
</style>

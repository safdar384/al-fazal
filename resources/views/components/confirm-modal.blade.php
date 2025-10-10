<!-- Reusable Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded-3">
            
            <!-- Header -->
            <div class="modal-header text-white" id="confirmHeader">
                <h5 class="modal-title fw-bold" id="confirmTitle">Confirm</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body text-center fs-6" id="confirmMessage">
                Are you sure?
            </div>

            <!-- Footer -->
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn px-4" id="confirmOk">
                    <i class="bi bi-check-circle me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

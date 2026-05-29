{{-- Modal: Edit Log --}}
<div class="modal fade" id="userLogsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-history mr-2"></i>Riwayat Perubahan — <span id="logs-user-name">-</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="logs-loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat log...</p>
                </div>
                <div id="logs-empty" class="text-center py-4 text-muted" style="display:none;">
                    Belum ada riwayat perubahan.
                </div>
                <div id="logs-content" style="display:none;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Admin</th>
                                <th>Field</th>
                                <th>Sebelum</th>
                                <th>Sesudah</th>
                            </tr>
                        </thead>
                        <tbody id="logs-tbody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

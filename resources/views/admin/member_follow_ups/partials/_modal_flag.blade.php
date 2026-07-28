{{-- Modal: Mark as Need Follow Up (shared — triggered from Users page and Follow-Up page) --}}
<div class="modal fade" id="followUpModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-people-arrows mr-1"></i>Mark as Need Follow Up</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    Member: <strong id="fu-member-name">-</strong><br>
                    <small class="text-muted">Company saat ini: <span id="fu-current-company">-</span></small>
                </p>
                <input type="hidden" id="fu-user-id">
                <div class="form-group">
                    <label>Company Terbaru <span class="text-danger">*</span></label>
                    <input type="text" id="fu-new-company-name" class="form-control" list="fu-company-datalist"
                        placeholder="Ketik nama company terbaru...">
                    <datalist id="fu-company-datalist">
                        @foreach ($companyNames ?? [] as $name)
                            <option value="{{ $name }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="form-group mb-0">
                    <label>Notes (opsional)</label>
                    <textarea id="fu-notes" rows="3" class="form-control" placeholder="Info tambahan untuk follow up..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="fu-btn-submit">
                    <i class="fas fa-people-arrows mr-1"></i> Mark as Need Follow Up
                </button>
            </div>
        </div>
    </div>
</div>

@push('bottom')
<script>
    (function() {
        $(document).on('click', '.btn-open-follow-up-modal', function() {
            var btn = $(this);
            $('#fu-user-id').val(btn.data('user-id'));
            $('#fu-member-name').text(btn.data('member-name') || '-');
            $('#fu-current-company').text(btn.data('current-company') || '-');
            $('#fu-new-company-name').val(btn.data('new-company') || '');
            $('#fu-notes').val(btn.data('notes') || '');
            $('#followUpModal').modal('show');
        });

        $(document).on('click', '#fu-btn-submit', function() {
            var btn = $(this);
            var newCompany = $('#fu-new-company-name').val().trim();
            if (!newCompany) {
                toastr.error('Company terbaru wajib diisi.');
                return;
            }

            btn.prop('disabled', true);
            $.ajax({
                url: '{{ route('admin.member_follow_ups.store') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    user_id: $('#fu-user-id').val(),
                    new_company_name: newCompany,
                    notes: $('#fu-notes').val()
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        location.reload();
                    } else {
                        toastr.error(res.message || 'Gagal menyimpan follow up.');
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan.';
                    toastr.error(msg);
                    btn.prop('disabled', false);
                }
            });
        });

        $('#followUpModal').on('hidden.bs.modal', function() {
            $('#fu-btn-submit').prop('disabled', false);
        });
    })();
</script>
@endpush

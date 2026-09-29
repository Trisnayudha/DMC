{{-- Modal: Register visitor as member — asks which channel was used to
     approach them (Sponsor referral / WhatsApp / Email), saved into
     users.hear (sama seperti "How did you hear about us?" member biasa). --}}
<div class="modal fade" id="registerAsMemberModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" id="registerAsMemberForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus mr-1"></i>Register as Member</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Daftarkan <strong id="ram-visitor-name">-</strong> sebagai calon member?</p>
                    <div class="form-group mb-0">
                        <label>Channel <span class="text-danger">*</span></label>
                        <select name="channel" class="form-control" required>
                            <option value="">Pilih channel...</option>
                            <option value="Sponsor">Sponsor</option>
                            <option value="WA">WhatsApp</option>
                            <option value="Email">Email</option>
                        </select>
                        <small class="text-muted">Cara visitor ini di-approach untuk jadi calon member — tersimpan di data member (mirip "How did you hear about us?").</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftarkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Register visitor as member — sama persis 3 checkbox consent yang
     ada di form registrasi member publik (FormMemberController::store()),
     supaya visitor yang didaftarkan lewat sini punya data consent yang sama
     kelakuannya dengan yang daftar sendiri lewat web. --}}
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
                    <div class="form-group">
                        <label for="ram_company_category">Company Category <span class="text-danger">*</span></label>
                        <select name="company_category" id="ram_company_category" class="form-control" required>
                            @include('partials._company_category_options')
                        </select>
                    </div>

                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="ram_newsletter" name="newsletter" value="agree" required checked>
                        <label class="custom-control-label" for="ram_newsletter">I agree to receive newsletters and program updates via email. <span class="text-danger">*</span></label>
                    </div>
                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="ram_wa_updates" name="wa_updates" value="agree">
                        <label class="custom-control-label" for="ram_wa_updates">I agree to receive updates via WhatsApp.</label>
                    </div>
                    <div class="custom-control custom-checkbox mb-0">
                        <input type="checkbox" class="custom-control-input" id="ram_explore" name="explore" value="agree">
                        <label class="custom-control-label" for="ram_explore">I would like to receive information about corporate sponsorship opportunities.</label>
                    </div>
                    <small class="text-muted d-block mt-2">Sama seperti form registrasi member di web — centang "sponsorship opportunities" otomatis memasukkan member ini ke Lead Follow-Up.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftarkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

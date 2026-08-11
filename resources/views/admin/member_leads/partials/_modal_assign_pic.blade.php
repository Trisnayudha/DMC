{{-- Modal: Assign PIC to a lead --}}
<div class="modal fade" id="assignPicModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-tag mr-1"></i>Assign PIC</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Lead: <strong id="ap-member-name">-</strong></p>
                <div class="form-group mb-0">
                    <label>PIC Members Relation</label>
                    <select id="ap-pic-id" class="form-control">
                        <option value="">— Pilih PIC —</option>
                        @foreach ($pics as $pic)
                            <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="ap-btn-submit">
                    <i class="fas fa-user-tag mr-1"></i> Assign
                </button>
            </div>
        </div>
    </div>
</div>

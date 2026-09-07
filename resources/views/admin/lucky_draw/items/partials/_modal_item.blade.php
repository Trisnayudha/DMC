{{-- Modal: Tambah / Edit item hadiah --}}
<div class="modal fade" id="luckyDrawItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="lucky-draw-item-form" action="{{ route('admin.lucky_draw.items.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lucky-draw-item-modal-title">
                        <i class="fas fa-gift mr-1"></i>Add Prize
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Prize Name</label>
                        <input type="text" name="name" id="ldi-name" class="form-control" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label>Photo <span class="text-muted font-weight-normal">(optional)</span></label>
                        <input type="file" name="image" id="ldi-image" class="form-control-file" accept="image/*">
                        <div class="mt-2">
                            <img id="ldi-image-preview" src="" alt="" width="72" style="display:none;border-radius:6px;">
                        </div>
                        <small class="text-muted">Can be left blank — without a photo, the public page falls back to a
                            🎁 icon + the prize name. When editing, leave empty to keep the existing photo.</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label>Chance (%)</label>
                            <input type="number" name="chance_percent" id="ldi-chance" class="form-control" required
                                min="0" max="100" step="0.01">
                            <small class="text-muted">0% = will never be drawn.</small>
                        </div>
                        <div class="form-group col-6">
                            <label>Display Order</label>
                            <input type="number" name="sort_order" id="ldi-sort" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group form-check mb-0">
                        <input type="checkbox" class="form-check-input" name="is_active" id="ldi-active" value="1"
                            checked>
                        <label class="form-check-label" for="ldi-active">Active (included in the draw)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

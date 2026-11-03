<div class="step-pane" data-step="1">
    <h2 class="panel-header-title">1. Member Information Update</h2>

    <div class="row">
        <div class="col-md-6 form-group">
            <label for="full_name">Full Name <span class="req-star">*</span></label>
            <input type="text" name="full_name" id="full_name" class="form-control-dark" value="{{ old('full_name') }}" required>
            <div class="invalid-feedback-custom">This field is required.</div>
        </div>

        <div class="col-md-6 form-group">
            <label for="company">Company / Organization <span class="req-star">*</span></label>
            <input type="text" name="company" id="company" class="form-control-dark" value="{{ old('company') }}" required>
            <div class="invalid-feedback-custom">This field is required.</div>
        </div>

        <div class="col-md-6 form-group">
            <label for="position">Position / Title <span class="req-star">*</span></label>
            <input type="text" name="position" id="position" class="form-control-dark" value="{{ old('position') }}" required>
            <div class="invalid-feedback-custom">This field is required.</div>
        </div>

        <div class="col-md-6 form-group">
            <label for="email">Email <span class="req-star">*</span></label>
            <input type="email" name="email" id="email" class="form-control-dark" value="{{ old('email') }}" required>
            <div class="invalid-feedback-custom">This field is required.</div>
        </div>

        <div class="col-md-6 form-group">
            <label for="phone">Mobile Number <span class="req-star">*</span></label>
            <input type="tel" name="phone" id="phone" class="form-control-dark" value="{{ old('phone') }}" required>
            <div class="invalid-feedback-custom">This field is required.</div>
        </div>

        <div class="col-md-6 form-group">
            <label for="linkedin">LinkedIn / Professional Profile</label>
            <input type="text" name="linkedin" id="linkedin" class="form-control-dark" value="{{ old('linkedin') }}">
        </div>
    </div>
</div>

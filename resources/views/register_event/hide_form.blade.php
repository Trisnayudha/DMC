<div class="hide" id="blank_row">
    <div class="group_row form_row">
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="name" class="form-label">Full name *</label>
                    <input type="text" class="form-control name" name="name" placeholder="" value="" required>
                    <div class="invalid-feedback">
                        Valid name is required.
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <label for="email" class="form-label">Email Address * <span class="text-muted"></span></label>
                <input type="email" class="form-control email" name="email" id="email"
                    placeholder="Your work email" required value="">
                <div class="invalid-feedback">
                    Please enter a valid email address.
                </div>
            </div>
            <div class="col-sm-6">
                <label for="phone" class="form-label">Mobile number *</label>
                <input type="tel" class="phone form-control phone" name="phone" placeholder="" value=""
                    required>
                <div class="invalid-feedback">
                    Please provide a Mobile Number
                </div>
            </div>
            <div class="col-sm-6">
                <label for="job_title" class="form-label">Job Title *</label>
                <input type="text" class="form-control job_title" name="job_title" id="job_title" placeholder=""
                    required value="">
                <div class="invalid-feedback">
                    Please enter your Job Title.
                </div>
            </div>
            <div class="col-sm-3 mb-3">
                <label for="company_name" class="form-label" style="color:white">.</label>
                <select class="form-select" id="prefix" name="prefix" required>
                    <option value="PT">PT</option>
                    <option value="CV">CV</option>
                    <option value="Ltd">Ltd</option>
                    <option value="GmbH">GmbH</option>
                    <option value="Limited">Limited</option>
                    <option value="Llc">Llc</option>
                    <option value="Corp">Corp</option>
                    <option value="Pte Ltd">Pte Ltd</option>
                    <option value="Assosiation">Assosiation</option>
                    <option value="Government">Government</option>
                    <option value="Pty Ltd">Pty Ltd</option>
                    <option value="">Other</option>
                </select>
                <div class="invalid-feedback">
                    Please select a valid prefix company name.
                </div>
            </div>
            <div class="col-sm-9">
                <label for="company" class="form-label" style="color:white">.</label>
                <input type="text" class="form-control company" name="company" id="company"
                    placeholder="Company Name" required value="">
                <div class="invalid-feedback">
                    Please enter your Job Title.
                </div>
            </div>
            <div class="col-sm-12 mt-2">
                <div class="btn btn-danger float-right form_item remove_row"> Remove</div>
            </div>
        </div>
    </div>
</div>

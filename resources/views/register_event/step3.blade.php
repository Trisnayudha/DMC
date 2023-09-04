<hr class="my-4">
<h4 class="mb-3">* Booking Contact</h4>
<div class="alert alert-warning" role="alert">
    Enter the best person we can contact for this booking in the event of unplanned changes.
</div>
<div class="row g-3">
    <div class="col-md-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="checkbox_data">
            <label class="form-check-label" for="checkbox_data">
                Same as contact person
            </label>
        </div>
    </div>
    <div class="col-md-2">
        <label for="company_name" class="form-label" style="color:white">.</label>
        <select class="form-select" id="prefix_contact" name="prefix_contact" required>
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
    <div class="col-sm-4">
        <label for="company_name" class="form-label" style="color: white">. </label>
        <input type="text" class="form-control" name="company_name" id="company_name" placeholder="Your company name"
            value="{{ old('company_name') }}" required>
        <div class="invalid-feedback">
            Valid company name is required.
        </div>
    </div>

    <div class="col-sm-6">
        <label for="name" class="form-label">Full name *</label>
        <input type="text" class="form-control" name="name_contact" id="name_contact" placeholder=""
            value="{{ old('name') }}" required>
        <div class="invalid-feedback">
            Valid name is required.
        </div>
    </div>
    <div class="col-sm-6">
        <label for="phone" class="form-label">Mobile number *</label>
        <input type="tel" class="form-control" name="phone_contact" id="phone_contact" placeholder=""
            value="{{ old('phone') ? old('phone') : '+62' }}" required>
        <div class="invalid-feedback">
            Please provide a Mobile Number
        </div>
    </div>
    <div class="col-sm-6">
        <label for="job_title" class="form-label">Job Title *</label>
        <input type="text" class="form-control" name="job_title_contact" id="job_title_contact" placeholder=""
            required value="{{ old('job_title') }}">
        <div class="invalid-feedback">
            Please enter your Job Title.
        </div>
    </div>
    <div class="col-sm-6">
        <label for="email" class="form-label">Email Address * <span class="text-muted"></span></label>
        <input type="email" class="form-control" name="email_contact" id="email_contact" placeholder="Your work email"
            required value="{{ old('email') }}">
        <div class="invalid-feedback">
            Please enter a valid email address.
        </div>
    </div>

    <div class="col-sm-6">
        <label for="company_website" class="form-label">Company Webstie *<span class="text-muted"></span></label>
        <input type="text" class="form-control" name="company_website" value="{{ old('company_website') }}"
            placeholder="www.yourcompany.com" required>
        <div class="invalid-feedback">
            Please enter a valid company website .
        </div>
    </div>


    <div class="col-sm-12">
        <label for="address" class="form-label">Address *</label>
        <input type="text" class="form-control" name="address" placeholder="" required
            value="{{ old('address') }}">
        <div class="invalid-feedback">
            Please provide a Mobile Number
        </div>
    </div>
    <div class="col-sm-6">
        <label for="office_number" class="form-label">Office Number</label>
        <input type="tel" class="form-control" name="office_number"id="office_number" placeholder=""
            value="{{ old('office_number') ? old('office_number') : '+62' }}" required>
        <div class="invalid-feedback">
            Please provide a Mobile Number
        </div>
    </div>
    <div class="col-sm-3">
        <label for="portal_code" class="form-label">Postal Code</label>
        <input type="number" class="form-control" name="portal_code" placeholder="" required
            value="{{ old('portal_code') }}">
        <div class="invalid-feedback" {{ old('portal_code') }}>
            Please provide a Postal Code
        </div>
    </div>
    <div class="col-sm-3">
        <label for="city" class="form-label">City</label>
        <input type="text" class="form-control" name="city" placeholder="" required
            value="{{ old('city') }}">
        <div class="invalid-feedback" {{ old('city') }}>
            Please provide a City
        </div>
    </div>

    <div class="col-sm-6 mb-3">
        <label for="country" class="form-label">Country * </label>
        <select class="form-control js-example-basic-single" name="country" id="country" placeholder="" required>
            <option value="Indonesia" selected>Indonesia</option>
        </select>
        <div class="invalid-feedback">
            Please provide a valid Country
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="company_category" class="form-label">Company Category *</label>
        <select class="form-control js-example-basic-single d-block w-100" name="company_category"
            id="company_category" required>
            <option value="">--Select--</option>
            <option value="Coal Mining">Coal Mining</option>
            <option value="Minerals Producer">Minerals Producer</option>
            <option value="Supplier/Distributor/Manufacturer">
                Supplier/Distributor/Manufacturer
            </option>
            <option value="Contrator">Contrator</option>
            <option value="Association / Organization / Government">
                Association / Organization / Government</option>
            <option value="Financial Services">Financial Services</option>
            <option value="Technology">Technology</option>
            <option value="Investors">Investors</option>
            <option value="Logistics and Shipping">Logistics and Shipping</option>
            <option value="Media">Media</option>
            <option value="Consultant">Consultant</option>
            <option value="other">Other</option>
        </select>
        <div class="invalid-feedback">
            Please enter your Company Other
        </div>
    </div>

    <div class="col-md-12 mb-6">
        <div class="myDiv">
            <label for="company_other" class="form-label">Company Other *</label>
            <input type="text" class="form-control" name="company_other" placeholder="">
            <div class="invalid-feedback">
                Please enter your Company Other
            </div>
        </div>
    </div>

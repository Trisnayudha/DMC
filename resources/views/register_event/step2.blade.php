<legend>* ATTENDEES</legend>
<input type="hidden" data-action-type="submit" data-event-type="theatre" id="formType">
<div class="row g-5">
    <div class="col-md-8 order-md-1">
        <h4 class="mb-3"></h4>
        <div class="alert alert-info" role="alert">
            Enter Attendees Details Here
        </div>
        <div class="form-check member">
            <input id="credit" name="paymentMethod" type="radio" class="form-check-input" required value="member"
                disabled>
            <label class="form-check-label" for="credit">Member (Rp. 900.000)</label>
        </div>
        <div class="form-check non-member">
            <input id="debit" name="paymentMethod" type="radio" class="form-check-input" required
                value="nonmember" disabled>
            <label class="form-check-label" for="debit">Non Member (Rp.
                1.000.000)</label>
        </div>
        <div class="form-check member">
            <input id="onsite" name="paymentMethod" type="radio" class="form-check-input" required value="onsite"
                disabled>
            <label class="form-check-label" for="onsite">On Site (Rp.
                1.250.000)</label>
        </div>
        <hr class="my-4">
        <!--dynamic table limit will be needed here for foundation members-->
        <div id="your_group">
            <div class="group_table">
                <div class="group_rows">
                    <div class="group_row form_row ">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">Full name
                                        *</label>
                                    <input type="text" class="form-control name" name="name" id="name"
                                        placeholder="" value="" required>
                                    <div class="invalid-feedback">
                                        Valid name is required.
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="email" class="form-label">Email Address
                                    *
                                    <span class="text-muted"></span></label>
                                <input type="email" class="form-control email" name="email" id="email"
                                    placeholder="Your work email" required value="">
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="phone" class="form-label">Mobile number
                                    *</label>
                                <input type="tel" class="phone form-control" name="phone" id="phone"
                                    placeholder="" value="{{ old('phone') ? old('phone') : '+62' }}" required>
                                <div class="invalid-feedback">
                                    Please provide a Mobile Number
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label for="job_title" class="form-label">Job Title
                                    *</label>
                                <input type="text" class="form-control job_title" name="job_title" id="job_title"
                                    placeholder="" required value="">
                                <div class="invalid-feedback">
                                    Please enter your Job Title.
                                </div>
                            </div>
                            <div class="col-sm-3  mb-3">
                                <label for="company_name" class="form-label" style="color:white">.</label>
                                <select class="form-select company" id="prefix" name="prefix" required>
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
                                <input type="text" class="form-control" name="company" id="company"
                                    placeholder="Company Name" required value="">
                                <div class="invalid-feedback">
                                    Please enter your Job Title.
                                </div>
                            </div>

                            <div class="col-sm-12 mt-2">
                                <div class="form_row items2 add_buttons">
                                    <div class="form_item">
                                        <a href="javascript:void(0)"
                                            class="btn btn-primary float-right add_group_members" data-seat-limit="10"
                                            alt="Add Guest">Add
                                            Guest</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="slug" id="slug" value="">
        <hr class="my-4">
    </div>
    <div class="col-md-4 order-md-2 mb-4">
        <h4 class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted">No of Attendees</span>
            <span class="badge bg-info rounded-pill attend" data-total-attendees="1">1</span>
        </h4>
        <ul class="list-group mb-3 border" id="attendees-list">

        </ul>

        <li class="list-group-item d-flex justify-content-between border-2">
            <span>Total (Rp)</span>
            <strong class="total_price">Rp. 0</strong>
        </li>

    </div>

</div>

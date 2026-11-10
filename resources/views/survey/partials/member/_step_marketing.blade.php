<div class="step-pane" data-step="3">
    <div class="step-head">
        <h2 class="panel-header-title">3. Marketing & Communications Awareness</h2>
    </div>

    <div class="form-group">
        <label>Do you still receive emails (EDM) from DMC? <span class="req-star">*</span></label>
        @include('survey.partials.member._choice', [
            'name' => 'edm_received',
            'type' => 'radio',
            'options' => ['Yes', 'No', 'Not sure'],
            'required' => true,
        ])
    </div>

    <div class="form-group">
        <label>How frequently would you prefer to receive emails from DMC?</label>
        @include('survey.partials.member._choice', [
            'name' => 'email_frequency',
            'type' => 'radio',
            'options' => ['1 email per week', '2 emails per week', '3 emails per week', 'Only for important announcements and event updates'],
        ])
    </div>

    <div class="form-group">
        <label>What is your primary goal when opening emails from the Djakarta Mining Club?</label>
        @include('survey.partials.member._choice', [
            'name' => 'email_primary_goal',
            'type' => 'radio',
            'options' => [
                'Seeking information on upcoming events (Registration / Agenda)',
                'Obtaining industry news and updates',
                'Looking for networking opportunities',
                'Other',
            ],
            'cols' => 1,
        ])
        <input type="text" name="email_primary_goal_other" id="email_primary_goal_other"
            class="form-control-dark mt-2" placeholder="If other, please specify"
            value="{{ old('email_primary_goal_other') }}">
        <div class="invalid-feedback-custom">Please specify your answer.</div>
    </div>

    <div class="form-group">
        <label>Are you aware of and have you used the Djakarta Mining Club mobile app and website? <span class="req-star">*</span></label>
        @include('survey.partials.member._choice', [
            'name' => 'app_awareness',
            'type' => 'radio',
            'options' => \App\Models\DmcMemberSurvey::OPTIONS['app_awareness'],
            'required' => true,
            'cols' => 1,
        ])
    </div>

    <div class="form-group">
        <label>Which DMC social media platforms do you currently follow? <small><i>(Select all that apply)</i></small></label>
        @include('survey.partials.member._choice', [
            'name' => 'platforms',
            'type' => 'checkbox',
            'options' => ['LinkedIn', 'Instagram', 'Facebook', 'YouTube', 'WhatsApp Channel', "I don't follow any"],
        ])
    </div>

    <div class="form-group">
        <label for="communication_feedback">Do you have any suggestions to improve DMC's marketing communications and social media engagement?</label>
        <textarea name="communication_feedback" id="communication_feedback" rows="3" class="form-control-dark">{{ old('communication_feedback') }}</textarea>
    </div>
</div>

<div class="step-pane" data-step="2">
    <div class="step-head">
        <h2 class="panel-header-title">2. Program Expectations & Feedback</h2>
    </div>

    <div class="form-group">
        <label>Which types of programs would you like us to organize in 2027? <span class="req-star">*</span></label>
        @include('survey.partials.member._choice', [
            'name' => 'event_types',
            'type' => 'checkbox',
            'options' => ['Seminar/ Workshops/ Training Session', 'Conference/ Panel Discussion/ Expert Talk', 'Member Meetup/ Business Networking/ Informal Gatherings', 'Charity Events/ Community Engagement', 'Sport Activites/ Festive Celebrations', 'Other'],
            'labels' => ['Other' => 'Others : Please Specify'],
            'required' => true,
        ])
        <input type="text" name="event_types_other" id="event_types_other" class="form-control-dark mt-2" placeholder="If other, please specify" value="{{ old('event_types_other') }}">
        <div class="invalid-feedback-custom">Please specify your answer.</div>
    </div>

    <div class="form-group">
        <label for="topics_interest">What topics would you like to see featured in our 2027 programs?</label>
        <textarea name="topics_interest" id="topics_interest" rows="3" class="form-control-dark" placeholder="Please share the industry topics or specific areas you would like DMC to cover in its 2027 programs.">{{ old('topics_interest') }}</textarea>
    </div>

    <div class="form-group">
        <label for="speaker_wishlist">Do you have any suggestions for speakers or industry experts we should invite to DMC's 2027 programs?</label>
        <textarea name="speaker_wishlist" id="speaker_wishlist" rows="3" class="form-control-dark">{{ old('speaker_wishlist') }}</textarea>
    </div>

    <div class="form-group">
        <label>2027 Outstanding Contribution to the Indonesian Mining Industry Award - Nomination</label>
        <input type="text" name="nominee_name" class="form-control-dark mb-2" value="{{ old('nominee_name') }}"
            placeholder="Name of nominee">
        <input type="text" name="nominee_company" class="form-control-dark mb-2" value="{{ old('nominee_company') }}"
            placeholder="Company / Institution">
        <input type="text" name="nominee_reason" class="form-control-dark" value="{{ old('nominee_reason') }}" placeholder="Reason for Nomination">
    </div>

    <div class="form-group">
        <label>What aspects of DMC programs would you like us to improve? (Select all that apply)</label>
        @include('survey.partials.member._choice', [
            'name' => 'event_improvement_aspects',
            'type' => 'checkbox',
            'options' => ['Program Topics & Content', 'Speakers & Presentation Quality', 'Program Format & Duration', 'Networking Opportunities', 'Venue & Facilities', 'Other'],
        ])
        <input type="text" name="event_improvement_other" id="event_improvement_other" class="form-control-dark mt-2" placeholder="If other, please specify" value="{{ old('event_improvement_other') }}">
        <div class="invalid-feedback-custom">Please specify your answer.</div>
    </div>
</div>

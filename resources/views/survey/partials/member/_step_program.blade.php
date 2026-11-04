<div class="step-pane" data-step="2">
    <h2 class="panel-header-title">2. Program Expectations & Input</h2>

    <div class="form-group">
        <label>Which types of events would you like us to organize in 2027? <span class="req-star">*</span></label>
        @include('survey.partials.member._choice', [
            'name' => 'event_types',
            'type' => 'checkbox',
            'options' => ['Workshops', 'Seminars', 'Networking Events', 'Webinars', 'Other'],
            'required' => true,
        ])
        <input type="text" name="event_types_other" id="event_types_other" class="form-control-dark mt-2" placeholder="If other, please specify" value="{{ old('event_types_other') }}">
    </div>

    <div class="form-group">
        <label for="topics_interest">What topics are you most interested in 2027 programs?</label>
        <textarea name="topics_interest" id="topics_interest" rows="3" class="form-control-dark" placeholder="Please share the industry topics or specific areas you would like DMC to cover in its 2027 programs.">{{ old('topics_interest') }}</textarea>
    </div>

    <div class="form-group">
        <label for="speaker_wishlist">Are there any speakers or industry experts you would like to recommend for DMC's 2027 programs?</label>
        <textarea name="speaker_wishlist" id="speaker_wishlist" rows="3" class="form-control-dark">{{ old('speaker_wishlist') }}</textarea>
    </div>

    <div class="form-group">
        <label>Nomination for Outstanding Contribution to the Indonesia Mining Industry Award 2027</label>
        <input type="text" name="nominee_name" class="form-control-dark mb-2" value="{{ old('nominee_name') }}"
            placeholder="Name of nominee">
        <input type="text" name="nominee_company" class="form-control-dark mb-2" value="{{ old('nominee_company') }}"
            placeholder="Company / Institution">
        <input type="text" name="nominee_reason" class="form-control-dark" value="{{ old('nominee_reason') }}" placeholder="Reason for Nomination">
    </div>

    <div class="form-group">
        <label>What aspects of DMC events would you like us to improve? (Select all that apply)</label>
        @include('survey.partials.member._choice', [
            'name' => 'event_improvement_aspects',
            'type' => 'checkbox',
            'options' => ['Event Topics & Content', 'Speakers & Presentations', 'Event Format & Duration', 'Networking Opportunities', 'Venue & Facilities', 'Other'],
        ])
        <input type="text" name="event_improvement_other" id="event_improvement_other" class="form-control-dark mt-2" placeholder="If other, please specify" value="{{ old('event_improvement_other') }}">
    </div>
</div>

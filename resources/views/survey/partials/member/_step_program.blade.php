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
    </div>

    <div class="form-group">
        <label for="topics_interest">What topics are you most interested in 2027 programs?</label>
        <textarea name="topics_interest" id="topics_interest" rows="3" class="form-control-dark">{{ old('topics_interest') }}</textarea>
    </div>

    <div class="form-group">
        <label for="speaker_wishlist">Do you have any wishlist for speakers for 2027 programs?</label>
        <textarea name="speaker_wishlist" id="speaker_wishlist" rows="3" class="form-control-dark">{{ old('speaker_wishlist') }}</textarea>
    </div>

    <div class="form-group">
        <label>Nomination for Outstanding Contribution to the Indonesia Mining Industry Award 2027</label>
        <input type="text" name="nominee_name" class="form-control-dark mb-2" value="{{ old('nominee_name') }}"
            placeholder="Name of nominee">
        <input type="text" name="nominee_company" class="form-control-dark" value="{{ old('nominee_company') }}"
            placeholder="Company / Institution">
    </div>

    <div class="form-group">
        <label for="event_improvement">Suggestions to improve past events</label>
        <textarea name="event_improvement" id="event_improvement" rows="3" class="form-control-dark">{{ old('event_improvement') }}</textarea>
    </div>
</div>

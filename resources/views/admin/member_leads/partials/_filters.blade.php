@php
    $dateFields = \App\Http\Controllers\Admin\MemberLeadFollowUpController::DATE_FIELDS;
    $moreFilters = (bool) ($dateFrom || $dateTo);
@endphp
<form action="{{ url('admin/leads') }}" method="GET" id="lead-filter-form" class="lead-filters">
    <input type="hidden" name="per_page" value="{{ $perPage }}">
    <div class="lf-row">
        <label class="lf-field lf-search">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" name="search" value="{{ $search }}" aria-label="Search leads" placeholder="Search by name, company, email, phone…">
        </label>
        <label class="lf-field">
            <i class="fas fa-thermometer-half" aria-hidden="true"></i>
            <select name="temperature" class="lead-auto-submit" aria-label="Temperature">
                <option value="">Temperature</option>
                @foreach (['hot' => 'HOT', 'warm' => 'WARM', 'cold' => 'COLD'] as $value => $label)
                    <option value="{{ $value }}" {{ $temperature === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="lf-field">
            <i class="fas fa-trophy" aria-hidden="true"></i>
            <select name="result" class="lead-auto-submit" aria-label="Result">
                @foreach (['all' => 'Result: All', 'pending' => 'Pending', 'win' => 'Win', 'loss' => 'Loss', 'do_not_send' => 'Excluded'] as $value => $label)
                    <option value="{{ $value }}" {{ $result === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="lf-field">
            <i class="far fa-calendar-alt" aria-hidden="true"></i>
            <select name="follow_up" class="lead-auto-submit" aria-label="Follow-up deadline">
                @foreach (['' => 'Follow-up: All', 'today' => 'Due Today', 'overdue' => 'Overdue', 'tomorrow' => 'Due Tomorrow'] as $value => $label)
                    <option value="{{ $value }}" {{ $followUp === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="lf-field">
            <i class="fas fa-ban" aria-hidden="true"></i>
            <select name="eligibility" class="lead-auto-submit" aria-label="Follow-up eligibility">
                <option value="">Follow-up Eligibility</option>
                <option value="eligible" {{ $eligibility === 'eligible' ? 'selected' : '' }}>Eligible</option>
                <option value="do_not_send" {{ $eligibility === 'do_not_send' ? 'selected' : '' }}>Do Not Send</option>
            </select>
        </label>
        <label class="lf-field">
            <i class="far fa-envelope" aria-hidden="true"></i>
            <select name="channel" class="lead-auto-submit" aria-label="Channel">
                <option value="">Channel</option>
                @foreach (['email' => 'Email', 'whatsapp' => 'WhatsApp', 'call' => 'Call', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" {{ $channel === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="lf-field">
            <i class="far fa-user" aria-hidden="true"></i>
            <select name="pic_id" class="lead-auto-submit" aria-label="PIC">
                <option value="">PIC</option>
                @foreach ($pics as $pic)
                    <option value="{{ $pic->id }}" {{ (string) $picId === (string) $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="lf-apply">Apply</button>
        <a href="{{ url('admin/leads?result=all') }}" class="lf-clear">Clear Filters</a>
        <button type="button" class="lf-more" data-toggle="collapse" data-target="#lead-more-filters" aria-controls="lead-more-filters" aria-expanded="{{ $moreFilters ? 'true' : 'false' }}"><i class="fas fa-filter mr-1"></i> More Filters</button>
    </div>
    <div class="collapse {{ $moreFilters ? 'show' : '' }}" id="lead-more-filters">
        <div class="lf-row lf-advanced">
            <label class="lf-field"><span>Date field</span>
                <select name="date_field" aria-label="Date field">
                    @foreach ($dateFields as $value => $label)
                        <option value="{{ $value }}" {{ $dateField === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="lf-field"><span>From</span><input type="date" name="date_from" value="{{ $dateFrom }}" aria-label="From date"></label>
            <label class="lf-field"><span>To</span><input type="date" name="date_to" value="{{ $dateTo }}" aria-label="To date"></label>
            <button type="submit" class="lf-apply">Apply dates</button>
        </div>
    </div>
</form>

            {{-- ═══ FILTER PANEL ═══ --}}
            <div class="card" style="border-top: 3px solid #6777ef;">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('sponsors.annual-report') }}" id="filterForm">
                        <div class="row align-items-end" style="gap-y: 8px;">
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Year</label>
                                <select name="year" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:90px;">
                                    @foreach($availableYears as $yr)
                                        <option value="{{ $yr }}" {{ $yr == $year ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Package</label>
                                <select name="package" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:120px;">
                                    <option value="">All Packages</option>
                                    <option value="platinum" {{ $package == 'platinum' ? 'selected' : '' }}>Platinum</option>
                                    <option value="gold"     {{ $package == 'gold'     ? 'selected' : '' }}>Gold</option>
                                    <option value="silver"   {{ $package == 'silver'   ? 'selected' : '' }}>Silver</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Type</label>
                                <select name="renewal_type" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:150px;">
                                    <option value="">All Types</option>
                                    <option value="renewal"     {{ $renewalType == 'renewal'     ? 'selected' : '' }}>Renewal</option>
                                    <option value="upgrade"     {{ $renewalType == 'upgrade'     ? 'selected' : '' }}>Upgrade</option>
                                    <option value="new"         {{ $renewalType == 'new'         ? 'selected' : '' }}>New Sponsor</option>
                                    <option value="new_member"  {{ $renewalType == 'new_member'  ? 'selected' : '' }}>New Member</option>
                                    <option value="not_renewed" {{ $renewalType == 'not_renewed' ? 'selected' : '' }}>Not Renewed</option>
                                </select>
                            </div>
                            <div class="col-auto flex-grow-1">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Search Company</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Search sponsor name…"
                                           value="{{ $search }}" style="min-width:200px;">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            @if($package || $renewalType || $search)
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm d-block">&nbsp;</label>
                                <a href="{{ route('sponsors.annual-report', ['year' => $year]) }}"
                                   class="btn btn-sm btn-light border text-danger">
                                    <i class="fas fa-times mr-1"></i> Reset
                                </a>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

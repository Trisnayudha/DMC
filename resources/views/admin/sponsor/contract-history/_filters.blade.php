            {{-- ═══ FILTER PANEL ═══ --}}
            <div class="card mb-3" style="border-top: 3px solid #6777ef;">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('sponsors.contract-history') }}" id="filterForm">
                        <div class="row align-items-end" style="gap-y: 8px;">
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Year</label>
                                <select name="year" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:90px;">
                                    <option value="">All Years</option>
                                    @foreach($availableYears as $yr)
                                        <option value="{{ $yr }}" {{ (string) $yr === (string) $year ? 'selected' : '' }}>{{ $yr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm font-weight-600 text-muted text-uppercase" style="font-size:10px;letter-spacing:.5px;">Status</label>
                                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:130px;">
                                    <option value="">All Status</option>
                                    <option value="renewed"     {{ $status == 'renewed'     ? 'selected' : '' }}>Renewed</option>
                                    <option value="not_renewed" {{ $status == 'not_renewed' ? 'selected' : '' }}>Not Renewed</option>
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
                            @if($year || $status || $package || $search)
                            <div class="col-auto">
                                <label class="col-form-label col-form-label-sm d-block">&nbsp;</label>
                                <a href="{{ route('sponsors.contract-history') }}"
                                   class="btn btn-sm btn-light border text-danger">
                                    <i class="fas fa-times mr-1"></i> Reset
                                </a>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

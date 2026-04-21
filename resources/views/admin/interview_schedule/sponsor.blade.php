@extends('layouts.inspire.master')

@section('content')
    <div class="content-wrapper">
        <section class="section">
            <div class="section-header">
                <h1>Sponsor Interview Schedule</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item"><a href="{{ Route('home') }}">Dashboard</a></div>
                    <div class="breadcrumb-item active">Interview Schedule</div>
                </div>
            </div>

            <div class="section-body">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center" style="gap:10px;">
                        <h4 class="mb-0">Submitted Interview Schedules</h4>
                        <span class="badge badge-primary">Total: {{ $list->count() }}</span>
                    </div>
                    <div class="card-body">

                        <form method="GET" action="{{ route('admin.interview-schedule.sponsor.index') }}" class="mb-3">
                            <div class="form-row align-items-end">
                                <div class="form-group col-md-3 mb-2">
                                    <label>Package</label>
                                    <select name="package" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        <option value="silver" {{ $filterPackage === 'silver' ? 'selected' : '' }}>Silver</option>
                                        <option value="gold" {{ $filterPackage === 'gold' ? 'selected' : '' }}>Gold</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4 mb-2">
                                    <label>Company</label>
                                    <select name="company" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        @foreach ($sponsors as $s)
                                            <option value="{{ $s->id }}" {{ $filterCompany === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 mb-2">
                                    <label>Time Slot</label>
                                    <select name="slot" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        @foreach ($slots as $slot)
                                            <option value="{{ $slot }}" {{ $filterSlot === $slot ? 'selected' : '' }}>{{ $slot }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-2 mb-2 d-flex" style="gap:6px;">
                                    <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                                    <a href="{{ route('admin.interview-schedule.sponsor.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table id="laravel_crud" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Submitted At</th>
                                        <th>Company</th>
                                        <th>Package</th>
                                        <th>Time Slot</th>
                                        <th># Interviewee</th>
                                        <th>Questions</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($list as $index => $row)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->created_at ? $row->created_at->format('d M Y H:i') : '-' }}</td>
                                            <td>{{ $row->company_name }}</td>
                                            <td><span class="badge badge-{{ $row->sponsor_package === 'gold' ? 'warning' : 'secondary' }}">{{ strtoupper($row->sponsor_package) }}</span></td>
                                            <td>{{ $row->preferred_time_slot }}</td>
                                            <td>{{ $row->number_of_interviewees }}</td>
                                            <td>{{ is_array($row->selected_questions) ? implode(', ', $row->selected_questions) : '-' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info view-detail"
                                                    data-toggle="modal" data-target="#detailModal"
                                                    data-company="{{ $row->company_name }}"
                                                    data-package="{{ strtoupper($row->sponsor_package) }}"
                                                    data-slot="{{ $row->preferred_time_slot }}"
                                                    data-created="{{ $row->created_at ? $row->created_at->format('d M Y H:i') : '-' }}"
                                                    data-interviewees='@json($row->interviewees)'
                                                    data-questions='@json($row->selected_questions)'>
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Interview Schedule Detail</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><strong>Company:</strong> <span id="d-company"></span></div>
                    <div class="mb-2"><strong>Package:</strong> <span id="d-package"></span></div>
                    <div class="mb-2"><strong>Time Slot:</strong> <span id="d-slot"></span></div>
                    <div class="mb-3"><strong>Submitted:</strong> <span id="d-created"></span></div>

                    <hr>
                    <h6>Interviewees</h6>
                    <div id="d-interviewees" class="mb-3"></div>

                    <h6>Selected Questions</h6>
                    <div id="d-questions"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('bottom')
    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#laravel_crud')) {
                $('#laravel_crud').DataTable().destroy();
            }

            $('#laravel_crud').DataTable({
                pageLength: 25,
                order: [
                    [1, 'desc']
                ]
            });

            var questionMap = @json($questionMap);

            $('.view-detail').on('click', function() {
                var interviewees = $(this).data('interviewees') || [];
                var questions = $(this).data('questions') || [];

                $('#d-company').text($(this).data('company') || '-');
                $('#d-package').text($(this).data('package') || '-');
                $('#d-slot').text($(this).data('slot') || '-');
                $('#d-created').text($(this).data('created') || '-');

                var intervieweeHtml = '';
                if (interviewees.length) {
                    interviewees.forEach(function(item, idx) {
                        intervieweeHtml += '<div class="border rounded p-2 mb-2">';
                        intervieweeHtml += '<strong>#' + (idx + 1) + '</strong><br>';
                        intervieweeHtml += 'Name: ' + (item.name || '-') + '<br>';
                        intervieweeHtml += 'Job Title: ' + (item.job_title || '-');
                        intervieweeHtml += '</div>';
                    });
                } else {
                    intervieweeHtml = '<div class="text-muted">No interviewee data.</div>';
                }
                $('#d-interviewees').html(intervieweeHtml);

                var questionHtml = '';
                if (questions.length) {
                    questions.forEach(function(no) {
                        questionHtml += '<div class="mb-2"><strong>#' + no + '</strong> ' + (questionMap[no] || '') + '</div>';
                    });
                } else {
                    questionHtml = '<div class="text-muted">No questions selected.</div>';
                }
                $('#d-questions').html(questionHtml);
            });
        });
    </script>
@endpush

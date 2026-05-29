{{-- Table: unregistered members (MemberModel) --}}
<table id="laravel_crud" class="table table-bordered table-hover">
    <thead class="thead-light">
        <tr>
            <th width="10px">No</th>
            <th>Date</th>
            <th>Name</th>
            <th>Company</th>
            <th>Job Title</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Category</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; ?>
        @foreach ($list as $post)
            <tr>
                <td>{{ $no++ }}</td>
                <td class="text-nowrap">
                    {{ date('d M Y', strtotime($post->created_at)) }}<br>
                    <small class="text-muted">{{ date('H:i', strtotime($post->created_at)) }}</small>
                </td>
                <td>{{ $post->name }}</td>
                <td>{{ $post->company_name }}</td>
                <td>{{ $post->job_title }}</td>
                <td><a href="mailto:{{ $post->email }}">{{ $post->email }}</a></td>
                <td class="text-nowrap">{{ $post->fullphone ?? $post->phone }}</td>
                <td>{{ $post->address }}</td>
                <td>{{ $post->company_category == 'other' ? $post->company_other : $post->company_category }}</td>
                <td>
                    @if ($post->exported_at)
                        <span class="btn btn-xs btn-secondary disabled"
                            title="Exported on {{ \Carbon\Carbon::parse($post->exported_at)->format('d M Y H:i') }}">
                            <i class="fas fa-check-circle"></i> Exported
                        </span>
                    @else
                        <a href="{{ route('admin.member.export', $post->id) }}"
                            class="btn btn-xs btn-success"
                            onclick="return confirm('Export member ini ke Users?')">
                            <i class="fas fa-file-export"></i> Export to Member
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

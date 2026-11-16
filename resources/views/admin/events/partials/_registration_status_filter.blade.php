{{-- Registration status filter (server-side ?status=), combinable with the Paid/Sponsor/Free tab and Sponsor Company. --}}
@php
    $currentStatus = request('status');
    $statusFilters = [
        ''         => ['label' => 'All Status', 'class' => 'btn-outline-secondary'],
        'waiting'  => ['label' => 'Waiting', 'class' => 'btn-outline-warning'],
        'rejected' => ['label' => 'Rejected', 'class' => 'btn-outline-danger'],
    ];
@endphp
<div class="btn-group btn-group-sm d-inline mr-2" role="group" aria-label="Registration status filter">
    @foreach ($statusFilters as $key => $meta)
        @php
            $query = array_merge(['slug' => $slug], request()->only('params', 'sponsor_id'));
            if ($key !== '') {
                $query['status'] = $key;
            }
        @endphp
        <a href="{{ route('events-details', $query) }}"
            class="btn {{ $meta['class'] }} {{ (string) $currentStatus === (string) $key ? 'active' : '' }}">{{ $meta['label'] }}</a>
    @endforeach
</div>

{{-- Row cell: job tier kecil yang bisa diubah langsung dari tabel (hanya member active).
     Expects $post, $tier (int|null), $manual (bool). Lebar select sengaja sempit: yang
     terlihat cuma "T1".."T4", keterangan lengkap muncul saat dropdown dibuka. --}}
@if ($tier)
    <select class="job-tier-select{{ $manual ? ' is-manual' : '' }}"
        data-url="{{ route('users.update.job_tier', $post->user_id) }}"
        data-current="{{ $tier }}"
        style="background-color: {{ \App\Support\JobTierClassifier::color($tier) }};"
        title="{{ \App\Support\JobTierClassifier::tooltip($tier, $manual) }}" data-toggle="tooltip">
        @foreach (\App\Support\JobTierClassifier::TIERS as $tierNo => $tierMeta)
            <option value="{{ $tierNo }}" {{ $tierNo === $tier ? 'selected' : '' }}>T{{ $tierNo }}&nbsp;&nbsp;&nbsp;— {{ $tierMeta['roles'] }}</option>
        @endforeach
        <option value="auto">Auto — ikut job title</option>
    </select>
@endif

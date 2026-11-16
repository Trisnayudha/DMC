{{-- ⓘ icon explaining where a diagram number comes from (Bootstrap popover, initialised in
     _converted_members_modal). Expects $title and $sources = [[label, count|null, explanation], ...];
     a null count shows the line as a plain note. Optional $placement (default right). --}}
@php
    $content = '';
    foreach ($sources as $source) {
        $heading = $source[1] === null ? e($source[0]) : e($source[0]) . ': ' . number_format((int) $source[1]);
        $content .= '<div class="mb-2"><strong>' . $heading . '</strong>'
            . '<div class="small text-muted">' . e($source[2]) . '</div></div>';
    }
@endphp
{{-- A <span>, not a link: it also sits inside clickable boxes, which are links themselves. --}}
<span class="stage-info {{ $iconClass ?? 'text-muted' }} ml-1" tabindex="0" role="button" style="cursor: help;"
    data-toggle="popover" data-trigger="hover focus" data-html="true" data-placement="{{ $placement ?? 'right' }}"
    data-title="{{ $title }}" data-content="{{ $content }}" aria-label="Where this number comes from">
    <i class="fas fa-info-circle"></i>
</span>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Renewal Form</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f6f7f7; margin: 0; padding: 24px; color: #333; }
        .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background: #c8611c; padding: 22px 28px; }
        .header .eyebrow { color: #fbe3cf; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; margin: 0 0 4px; }
        .header h2 { color: #fff; margin: 0; font-size: 18px; }
        .body { padding: 24px 28px; font-size: 13.5px; line-height: 1.55; }
        .intro { margin: 0 0 16px; }
        .alert-box { background: #fdf1e7; border-left: 4px solid #c8611c; padding: 12px 16px; border-radius: 4px; margin-bottom: 8px; font-size: 13px; }
        h3 { font-size: 13px; margin: 24px 0 10px; color: #c8611c; border-bottom: 1px solid #eee; padding-bottom: 6px; text-transform: uppercase; letter-spacing: .5px; }
        table.info { width: 100%; border-collapse: collapse; font-size: 13px; }
        table.info td { padding: 5px 0; vertical-align: top; }
        table.info td.label { width: 170px; color: #888; }
        table.info tr.total td { border-top: 1px solid #eee; padding-top: 8px; font-weight: bold; }
        table.changes { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        table.changes th { text-align: left; color: #888; font-weight: normal; border-bottom: 1px solid #eee; padding: 4px 6px 4px 0; }
        table.changes td { padding: 6px 6px 6px 0; vertical-align: top; border-bottom: 1px solid #f3f3f3; }
        .old { color: #b03a2e; text-decoration: line-through; }
        .new { color: #1e7e34; font-weight: bold; }
        .empty { font-size: 12px; color: #999; font-style: italic; }
        .meta { font-size: 12px; color: #888; margin-top: 22px; }
        .footer { background: #f8f9fa; padding: 14px 28px; font-size: 11px; color: #aaa; text-align: center; }
    </style>
</head>
<body>
@php
    $money = function ($value) {
        return $value === null ? '-' : number_format($value, 0, ',', '.');
    };
    $sponsorName = $sponsor->branding_name ?: $sponsor->name;
@endphp
<div class="card">
    <div class="header">
        <p class="eyebrow">Sponsorship Renewal &middot; {{ $isUpdate ? 'Updated' : 'New' }}</p>
        <h2>Renewal Form {{ $form->form_number }} &mdash; {{ $sponsorName }}</h2>
    </div>
    <div class="body">
        <p class="intro">Dear Secretariat Team,</p>
        <div class="alert-box">
            @if ($isUpdate)
                The renewal form <strong>{{ $form->form_number }}</strong> for <strong>{{ $sponsorName }}</strong> has been <strong>updated</strong>.
                The latest PDF is attached &mdash; please use this version and disregard any previous copies.
            @else
                A renewal form <strong>{{ $form->form_number }}</strong> has been generated for <strong>{{ $sponsorName }}</strong>.
                The PDF is attached to this email for your review and follow-up with the sponsor.
            @endif
        </div>

        @if ($isUpdate)
            <h3>Changes in This Update</h3>
            @if (!empty($changes))
                <table class="changes">
                    <tr><th style="width:34%;">Field</th><th style="width:33%;">Previous</th><th>Updated</th></tr>
                    @foreach ($changes as $label => $diff)
                        <tr>
                            <td>{{ $label }}</td>
                            <td class="old">{{ $diff['old'] !== '' ? $diff['old'] : '(empty)' }}</td>
                            <td class="new">{{ $diff['new'] !== '' ? $diff['new'] : '(empty)' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="empty">No field values changed; the PDF was regenerated with the same details.</div>
            @endif
        @endif

        <h3>Sponsor &amp; Contract</h3>
        <table class="info">
            <tr><td class="label">Sponsor</td><td><strong>{{ $sponsorName }}</strong>@if ($sponsorName !== $sponsor->name)<br><span style="color:#888;">{{ $sponsor->name }}</span>@endif</td></tr>
            <tr><td class="label">Package</td><td>{{ $package }}</td></tr>
            <tr><td class="label">Current Contract</td><td>{{ $periods['current'] }}</td></tr>
            <tr><td class="label">Renewal Period</td><td><strong>{{ $periods['renewal'] }}</strong></td></tr>
        </table>

        <h3>Billing Contact</h3>
        @if ($pic)
            <table class="info">
                <tr><td class="label">Name</td><td>{{ $pic->name ?: '-' }}</td></tr>
                <tr><td class="label">Position</td><td>{{ $pic->title ?: '-' }}</td></tr>
                <tr><td class="label">Email</td><td>{{ $pic->email ?: '-' }}</td></tr>
                <tr><td class="label">Phone</td><td>{{ $pic->phone ?: '-' }}</td></tr>
            </table>
        @else
            <div class="empty">No billing contact on record yet.</div>
        @endif

        <h3>Quotation</h3>
        <table class="info">
            <tr><td class="label">Form Number</td><td><strong>{{ $form->form_number }}</strong></td></tr>
            <tr><td class="label">Quotation Date</td><td>{{ $form->generated_at ? $form->generated_at->format('d F Y') : '-' }}</td></tr>
            <tr><td class="label">Exchange Rate (KMK)</td><td>IDR {{ $money($form->kmk_rate) }} / USD &middot; KMK No. {{ $form->kmk_number ?: '-' }}</td></tr>
            @if ($pricing['usd'] !== null)
                <tr><td class="label">Amount (USD)</td><td>USD {{ $money($pricing['usd']) }}</td></tr>
            @endif
            <tr><td class="label">Amount (IDR)</td><td>IDR {{ $money($pricing['idr']) }}</td></tr>
            @if ($pricing['vatPercent'] > 0)
                {{-- Sama dengan PDF: VAT + Grand Total IDR & USD (include VAT). --}}
                <tr>
                    <td class="label">VAT {{ rtrim(rtrim(number_format($pricing['vatPercent'], 2, '.', ''), '0'), '.') }}%</td>
                    <td>IDR {{ $money($pricing['vatIdr']) }}@if ($pricing['vatUsd'] !== null) &middot; USD {{ $money($pricing['vatUsd']) }}@endif</td>
                </tr>
                <tr class="total"><td class="label">Grand Total IDR</td><td>IDR {{ $money($pricing['grandIdr']) }} <span style="font-weight:normal;color:#888;">(incl. VAT)</span></td></tr>
                @if ($pricing['grandUsd'] !== null)
                    <tr><td class="label" style="font-weight:bold;">Grand Total USD</td><td><strong>USD {{ $money($pricing['grandUsd']) }}</strong> <span style="color:#888;">(incl. VAT)</span></td></tr>
                @endif
            @else
                <tr class="total"><td class="label">Total</td><td>IDR {{ $money($pricing['idr']) }}@if ($pricing['usd'] !== null) &middot; USD {{ $money($pricing['usd']) }}@endif <span style="font-weight:normal;color:#888;">(no VAT)</span></td></tr>
            @endif
        </table>

        @if ($form->notes)
            <h3>Notes</h3>
            <div>{!! nl2br(e($form->notes)) !!}</div>
        @endif

        <div class="meta">
            Generated by {{ $generatedBy }}@if ($updatedBy) &middot; Updated by {{ $updatedBy }}@endif &middot; {{ $sentAt }}
        </div>
    </div>
    <div class="footer">
        This is an automated notification from the Djakarta Mining Club system.
    </div>
</div>
</body>
</html>

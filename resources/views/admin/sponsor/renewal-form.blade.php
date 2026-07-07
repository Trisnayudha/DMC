<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Renewal Form – {{ $sponsor->name }}</title>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 40px 20px;
        background-color: #e9ecef;
        color: #000;
    }
    .page-container {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: #fff;
        padding: 18mm 16mm;
        box-sizing: border-box;
        box-shadow: 0 2px 16px rgba(0,0,0,.18);
    }
    /* Tombol aksi — hanya di layar (browser preview), tidak ikut ke PDF */
    .preview-toolbar {
        position: fixed;
        top: 16px;
        right: 20px;
        z-index: 1000;
        display: flex;
        gap: 8px;
    }
    .preview-toolbar a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #6777ef;
        color: #fff;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        padding: 9px 16px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(103,119,239,.4);
    }
    .preview-toolbar a.secondary { background: #495057; box-shadow: 0 2px 8px rgba(0,0,0,.2); }
    @media print { .preview-toolbar { display: none !important; } }

    /* HEADER */
    .top-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 8px;
    }
    .logo-side img { max-height: 60px; width: auto; display: block; }
    .address-side { text-align: right; font-size: 10.5px; line-height: 1.3; max-width: 450px; }
    .comp-name { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
    .header-divider { border: none; border-top: 2px solid #000; margin: 0 0 20px 0; }

    /* SUB-HEADER */
    .sub-header-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 20px;
        align-items: start;
        margin-bottom: 25px;
    }
    .meta-table { width: 100%; border-collapse: collapse; font-size: 11.5px; line-height: 1.4; }
    .meta-table td { padding: 2px 0; vertical-align: top; }
    .meta-table td:first-child { width: 120px; color: #000; }
    .title-box-side { display: flex; justify-content: flex-end; }
    .orange-title-box {
        background-color: #fdb813;
        border: 2px solid #000;
        width: 100%;
        padding: 10px;
        text-align: center;
        font-weight: bold;
        box-sizing: border-box;
    }
    .title-main { font-size: 15px; margin-bottom: 4px; letter-spacing: .5px; }
    .title-sub  { font-size: 13px; letter-spacing: .5px; }

    /* MAIN TABLE */
    .custom-table { width: 100%; border-collapse: collapse; font-size: 11.5px; line-height: 1.4; }
    .custom-table th,
    .custom-table td { border: 1px solid #000; padding: 6px 8px; }
    .custom-table th {
        background-color: #0044ff;
        color: #fff;
        font-weight: bold;
        text-align: center;
        font-size: 12px;
    }
    .text-center  { text-align: center; }
    .val-middle   { vertical-align: middle; }
    .font-bold    { font-weight: bold; }
    .desc-cell    { vertical-align: top; padding: 8px 10px; }
    .desc-main-title { font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
    .item-title   { font-weight: bold; margin-top: 6px; margin-bottom: 2px; }
    .item-sub     { padding-left: 2px; margin-bottom: 1px; }
    .item-sub-note { padding-left: 10px; font-size: 11px; margin-bottom: 2px; }
    .currency-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        box-sizing: border-box;
        padding: 0 4px;
    }
    .bg-dark { background-color: #4a4a4a; color: #fff; font-weight: bold; text-align: center; }

    /* FOOTER */
    .footer-section { margin-top: 25px; }
    .footer-section::after { content: ""; display: table; clear: both; }
    .notes-block {
        float: left;
        width: 55%;
        font-style: italic;
        font-size: 11.5px;
        line-height: 1.5;
    }
    .notes-title { font-weight: bold; margin-bottom: 4px; }
    .approval-block { float: right; width: 320px; text-align: center; margin-top: 50px; }
    .approved-text { font-style: italic; font-size: 12px; margin-bottom: 75px; }
    .signature-line { border-top: 1px solid #000; padding-top: 5px; font-size: 12px; }

    @media print {
        body { background: #fff; padding: 0; }
        .page-container { box-shadow: none; padding: 20px; width: 100%; }
    }
</style>
</head>
<body>
@php
    $pic = $sponsor->firstPic;

    $monthNames = [
        '01'=>'January','02'=>'February','03'=>'March','04'=>'April',
        '05'=>'May','06'=>'June','07'=>'July','08'=>'August',
        '09'=>'September','10'=>'October','11'=>'November','12'=>'December',
    ];

    // Nomor & tanggal: utamakan renewal form (proposal), fallback ke quotation kontrak.
    $renewalForm = $renewalForm ?? null;
    $quotNo = $renewalForm && $renewalForm->form_number
                    ? $renewalForm->form_number
                    : ($renewal ? ($renewal->quotation_number ?? '—') : '—');
    if ($renewalForm && $renewalForm->generated_at) {
        $quotDate = $renewalForm->generated_at->format('l, d F Y');
    } elseif ($renewal && $renewal->quotation_date) {
        $quotDate = $renewal->quotation_date->format('l, d F Y');
    } else {
        $quotDate = now()->format('l, d F Y');
    }

    // Nilai proposal: utamakan renewal form, fallback ke kontrak berjalan.
    $amountUsd = $renewalForm && $renewalForm->amount_usd !== null
                    ? $renewalForm->amount_usd
                    : ($renewal ? $renewal->amount_usd : null);
    $amountIdr = $renewalForm && $renewalForm->amount_idr !== null
                    ? $renewalForm->amount_idr
                    : ($renewal ? $renewal->amount_idr : null);

    $periodLabel = '—';
    if ($renewal && $renewal->contract_start && $renewal->contract_end) {
        [$sy, $sm] = explode('-', $renewal->contract_start);
        [$ey, $em] = explode('-', $renewal->contract_end);
        $periodLabel = ($monthNames[$sm] ?? $sm) . ' ' . $sy . '<br>' . ($monthNames[$em] ?? $em) . ' ' . $ey;
    }

    $packageLabel = strtoupper($sponsor->package ?? 'GOLD');
    $pkgMap = ['platinum' => 'PLATINUM / MAJOR', 'gold' => 'GOLD', 'silver' => 'SILVER'];
    $pkgDisplay = $pkgMap[$sponsor->package] ?? $packageLabel;

    // Warna banner header per paket: Gold = emas, Silver & Platinum/Major = abu (sama).
    $pkgColorMap = ['platinum' => '#a6a6a6', 'gold' => '#fdb813', 'silver' => '#a6a6a6'];
    $pkgColor = $pkgColorMap[$sponsor->package] ?? '#fdb813';
@endphp

@if(!empty($isPreview))
    <div class="preview-toolbar">
        <a href="{{ route('sponsors.renewal-form', $sponsor->id) }}">&#128190; Download PDF</a>
        <a href="javascript:window.print()" class="secondary">&#128424; Print</a>
    </div>
@endif

<div class="page-container">

    {{-- HEADER --}}
    <div class="top-header">
        <div class="logo-side">
            <img src="{{ asset('image/logo-dmc-cci3.png') }}" alt="DMC CCI Logo">
        </div>
        <div class="address-side">
            <div class="comp-name">Djakarta Mining Club and Coal Club Indonesia</div>
            <div>Gedung 47, Jalan Tb Simatupang no.47 Tanjung Barat Jagakarsa 12530</div>
            <div>T: 021 295 57233 &nbsp;E: secretariat@djakarta-miningclub.com / secretariat@coalclubindonesia.com</div>
        </div>
    </div>
    <hr class="header-divider">

    {{-- QUOTATION INFO + TITLE BOX --}}
    <div class="sub-header-grid">
        <div class="meta-side">
            <table class="meta-table">
                <tr>
                    <td>Quotation Date</td>
                    <td>{{ $quotDate }}</td>
                </tr>
                <tr>
                    <td>Quotation Number</td>
                    <td>{{ $quotNo }}</td>
                </tr>
                <tr>
                    <td style="vertical-align:top; padding-top:4px;">Attention</td>
                    <td style="padding-top:4px;">
                        @if($pic)
                            {{ $pic->name }}<br>
                            @if($pic->title) {{ $pic->title }}<br> @endif
                            <strong>{{ $sponsor->name }}</strong><br>
                            @if($sponsor->address) {{ $sponsor->address }}<br> @endif
                            @if($pic->phone) T: {{ $pic->phone }}@endif
                            @if($pic->email) &nbsp;- E: {{ $pic->email }} @endif
                        @else
                            <strong>{{ $sponsor->name }}</strong><br>
                            @if($sponsor->address) {{ $sponsor->address }}<br> @endif
                            @if($sponsor->email) E: {{ $sponsor->email }} @endif
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="title-box-side">
            <div class="orange-title-box" style="background-color: {{ $pkgColor }};">
                <div class="title-main">RENEWAL FORM</div>
                <div class="title-sub">DMC {{ $pkgDisplay }} SPONSORSHIP</div>
            </div>
        </div>
    </div>

    {{-- MAIN TABLE --}}
    <table class="custom-table">
        <thead>
            <tr>
                <th style="width:15%;">PERIOD</th>
                <th style="width:50%;">DESCRIPTION</th>
                <th style="width:15%;">QTY</th>
                <th style="width:20%;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center val-middle font-bold">{!! $periodLabel !!}</td>
                <td class="desc-cell">
                    <div class="desc-main-title">{{ $pkgDisplay }} SPONSOR OF DJAKARTA MINING CLUB</div>

                    @php $catIndex = 1; @endphp
                    @foreach($packageBenefits as $category => $items)
                        <div class="item-title">{{ $catIndex }}. {{ $category }}</div>
                        @foreach($items as $pb)
                            <div class="item-sub">- {{ $pb->benefit->name }}
                                @if($pb->quantity > 1)({{ $pb->quantity }}x)@endif
                            </div>
                            @if($pb->additional_info)
                                <div class="item-sub-note">{{ $pb->additional_info }}</div>
                            @endif
                            @if($pb->benefit->description)
                                <div class="item-sub-note">{{ $pb->benefit->description }}</div>
                            @endif
                        @endforeach
                        @php $catIndex++; @endphp
                    @endforeach
                </td>
                <td class="text-center val-middle font-bold">1 YEAR</td>
                <td class="val-middle font-bold">
                    <div class="currency-flex">
                        @if($amountUsd)
                            <span>USD</span>
                            <span>{{ number_format($amountUsd, 0, '.', '.') }}</span>
                        @elseif($amountIdr)
                            <span>IDR</span>
                            <span>{{ number_format($amountIdr, 0, '.', '.') }}</span>
                        @else
                            <span>—</span><span></span>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Totals rows --}}
            <tr>
                <td colspan="2" class="val-middle">
                    @if($kursRate)
                        <span style="display:inline-block;width:80px;">Kurs:</span> IDR {{ number_format($kursRate, 0, '.', '.') }}
                    @endif
                </td>
                <td class="bg-dark val-middle">Total In USD</td>
                <td class="val-middle font-bold">
                    <div class="currency-flex">
                        <span>USD</span>
                        <span>{{ $amountUsd ? number_format($amountUsd, 0, '.', '.') : '—' }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="val-middle">
                    @if(!empty($renewalForm) && $renewalForm->kmk_number)
                        KMK Nomor {{ $renewalForm->kmk_number }}
                    @elseif($kursRate)
                        KMK Nomor {{ now()->format('Y') }}/MK/EF.2/{{ now()->format('Y') }}
                    @endif
                </td>
                <td class="bg-dark val-middle">Total In IDR</td>
                <td class="val-middle font-bold">
                    <div class="currency-flex">
                        <span>IDR</span>
                        <span>
                            @if($amountIdr)
                                {{ number_format($amountIdr, 0, '.', '.') }}
                            @elseif($amountUsd && $kursRate)
                                {{ number_format($amountUsd * $kursRate, 0, '.', '.') }}
                            @else
                                —
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer-section">
        <div class="notes-block">
            <div class="notes-title">Notes:</div>
            <div>Confirmation no longer than 14 Day after Renewal form / Proposal Received</div>
            <div>Price are exclude VAT</div>
            <div>Payments available on Bank transfer and Credit Cards</div>
        </div>
        <div class="approval-block">
            <div class="approved-text">Approved by</div>
            <div class="signature-line">
                @if($pic) {{ $pic->name }} @else {{ $sponsor->name }} @endif
            </div>
        </div>
    </div>

</div>
</body>
</html>

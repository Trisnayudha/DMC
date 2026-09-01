<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Renewal Form – {{ $sponsor->name }}</title>
<style>
    /* dompdf punya default page margin bawaan 1.2cm (lib/res/html.css) yang nggak
       kepakai di sini — .page-container sudah punya padding sendiri buat itu.
       Kalau dibiarin default, lebar yang diminta (body padding + page-container
       210mm) lebih gede dari area cetak yang tersisa → kolom paling kanan
       (TOTAL) kepotong/ilang di hasil Download PDF. Wajib di-nol-in eksplisit,
       di luar media query — dompdf render semuanya sebagai media "screen"
       (config/dompdf.php: default_media_type), jadi @media print TIDAK PERNAH
       kepakai buat PDF, cuma buat Ctrl+P beneran dari browser. */
    @page { margin: 0; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #fff;
        color: #000;
    }
    /* Efek "halaman mengambang" di atas latar abu-abu — khusus live preview di
       browser (class ditambahin lewat JS di bawah). dompdf nggak eksekusi JS,
       jadi hasil PDF selalu pakai body polos di atas: nggak ada resiko lebar
       body-padding + page-container nabrak batas kertas. */
    body.is-live-preview {
        padding: 40px 20px;
        background-color: #e9ecef;
    }
    body.is-live-preview .page-container { box-shadow: 0 2px 16px rgba(0,0,0,.18); }
    @media print {
        body.is-live-preview { padding: 0; background-color: #fff; }
        body.is-live-preview .page-container { box-shadow: none; }
    }
    /* Lebar/tinggi dihitung sebagai content-box manual (210mm/297mm dikurangi
       padding) alih-alih pakai box-sizing:border-box — dompdf (CSS 2.1) nggak
       selalu patuh sama box-sizing, kalau kepeleset dianggap content-box beneran
       maka padding numpuk di LUAR 210mm dan konten kepotong di tepi kertas
       (kejadian kemarin). Math manual ini aman berapa pun dukungan dompdf-nya. */
    .page-container {
        width: 192mm;  /* 210mm - 9mm kiri - 9mm kanan */
        min-height: 265mm;  /* 297mm - 16mm atas - 16mm bawah */
        margin: 0 auto;
        background: #fff;
        padding: 16mm 9mm;
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

    /* HEADER — table-based, bukan flex: dompdf (v2.0.8) cuma CSS 2.1-compliant,
       flexbox/grid nggak reliable di sana (browser preview kelihatan bener tapi
       hasil Download PDF-nya berantakan). Table + float sudah dipakai di footer,
       ikutin pola yang sama biar konsisten di semua renderer. */
    .top-header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    .top-header-table td { border: none; padding: 0; vertical-align: bottom; }
    .logo-side { width: 40%; }
    .logo-side img { max-height: 60px; width: auto; display: block; }
    .address-side { width: 60%; text-align: right; font-size: 10.5px; line-height: 1.3; }
    .comp-name { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
    .header-divider { border: none; border-top: 2px solid #000; margin: 0 0 12px 0; }

    /* SUB-HEADER */
    .sub-header-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .sub-header-table td { border: none; padding: 0; vertical-align: top; }
    .meta-side { width: 58%; padding-right: 20px; }
    .meta-table { width: 100%; border-collapse: collapse; font-size: 11.5px; line-height: 1.4; }
    .sub-header-table .meta-table td { padding: 2px 0; vertical-align: top; }
    .meta-table td:first-child { width: 120px; color: #000; }
    .title-box-side { width: 42%; }
    /* Nggak dikasih width:100% + box-sizing — div block polos (width:auto) udah
       otomatis ngisi penuh sel induknya termasuk border+padding-nya, tanpa
       gantung ke box-sizing sama sekali. */
    .orange-title-box {
        background-color: #fdb813;
        border: 2px solid #000;
        padding: 10px;
        text-align: center;
        font-weight: bold;
    }
    .title-main { font-size: 15px; margin-bottom: 4px; letter-spacing: .5px; }
    .title-sub  { font-size: 13px; letter-spacing: .5px; }

    /* MAIN TABLE */
    /* table-layout:fixed — lebar kolom (15/50/15/20%) WAJIB dipatuhi persis, bukan
       cuma saran; tanpa ini dompdf bisa nyoba nge-lebarin kolom TOTAL ngikutin
       konten (nested currency-table di dalamnya), dorong dia kepotong di tepi
       kertas kayak yang kejadian kemarin. */
    .custom-table { width: 100%; table-layout: fixed; border-collapse: collapse; font-size: 10.5px; line-height: 1.3; }
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
    .desc-cell    { vertical-align: top; padding: 6px 8px; }
    .desc-main-title { font-weight: bold; text-transform: uppercase; margin-bottom: 4px; }
    .item-title   { font-weight: bold; margin-top: 5px; margin-bottom: 2px; }
    .item-sub     { padding-left: 2px; margin-bottom: 1px; }
    /* Selector diawali .custom-table biar specificity-nya ngalahin ".custom-table td"
       di atas, nggak gantung ke urutan aturan CSS (nested table ini duduk di dalam
       sel .custom-table, jadi kena juga selector induknya kalau nggak dikalahin). */
    .custom-table .currency-table { width: 100%; table-layout: fixed; border-collapse: collapse; }
    .custom-table .currency-table td { border: none; padding: 0 4px; }
    .custom-table .currency-table td:last-child { text-align: right; }
    .bg-dark { background-color: #4a4a4a; color: #fff; font-weight: bold; text-align: center; }

    /* FOOTER — spacing dirapetin (25→14, 50→18, 75→40px) biar baris "Approved by"
       nggak nyempil sendirian ke halaman 2, masih cukup ruang buat tanda tangan. */
    .footer-section { margin-top: 14px; }
    .footer-section::after { content: ""; display: table; clear: both; }
    .notes-block {
        float: left;
        width: 55%;
        font-style: italic;
        font-size: 11.5px;
        line-height: 1.5;
    }
    .notes-title { font-weight: bold; margin-bottom: 4px; }
    .approval-block { float: right; width: 320px; text-align: center; margin-top: 18px; }
    .approved-text { font-style: italic; font-size: 12px; margin-bottom: 40px; }
    .signature-line { border-top: 1px solid #000; padding-top: 5px; font-size: 12px; }
</style>
</head>
<body>
<script>
    // dompdf nggak eksekusi JS sama sekali, jadi class ini CUMA nempel pas dibuka
    // beneran di browser (preview) — nentuin efek "halaman mengambang" abu-abu
    // di atas. Diletakkan di awal body & synchronous biar kepasang sebelum paint,
    // nggak sempat keliatan flash body polos dulu.
    document.body.classList.add('is-live-preview');
</script>
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

    // VAT/PPN — optional, diisi tim finance lewat dropdown saat generate renewal
    // form (default 0% = nggak kena VAT). Cuma ada di renewalForm, nggak ada
    // fallback ke $renewal kontrak, karena field ini memang belum ada di sana.
    $vatPercent = $renewalForm && $renewalForm->vat_percent ? (float) $renewalForm->vat_percent : 0;

    // Total final (sebelum VAT) — sama persis kayak yang dihitung di baris
    // "Total In USD"/"Total In IDR" di bawah, dipakai lagi buat Grand Total.
    $totalUsd = $amountUsd ?: null;
    $totalIdr = $amountIdr !== null && $amountIdr !== ''
                    ? $amountIdr
                    : (($amountUsd && $kursRate) ? $amountUsd * $kursRate : null);

    $grandTotalUsd = ($vatPercent > 0 && $totalUsd !== null) ? $totalUsd * (1 + $vatPercent / 100) : null;
    $grandTotalIdr = ($vatPercent > 0 && $totalIdr !== null) ? $totalIdr * (1 + $vatPercent / 100) : null;
    // Nominal pajaknya doang (bukan grand total) — ditampilin di baris "VAT X%".
    $vatAmountIdr  = ($vatPercent > 0 && $totalIdr !== null) ? $totalIdr * ($vatPercent / 100) : null;

    $periodLabel = '—';
    if ($renewal && $renewal->contract_start && $renewal->contract_end) {
        [$sy, $sm] = explode('-', $renewal->contract_start);
        [$ey, $em] = explode('-', $renewal->contract_end);
        // Satu paragraf, wrap alami — muat 1 baris kalau kolomnya cukup lebar (kaya
        // referensi invoice lama), pecah sendiri ke baris ke-2 kalau kepanjangan.
        $periodLabel = ($monthNames[$sm] ?? $sm) . ' ' . $sy . ' - ' . ($monthNames[$em] ?? $em) . ' ' . $ey;
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
    <table class="top-header-table">
        <tr>
            <td class="logo-side">
                <img src="{{ asset('image/logo-dmc-cci3.png') }}" alt="DMC CCI Logo">
            </td>
            <td class="address-side">
                <div class="comp-name">Djakarta Mining Club and Coal Club Indonesia</div>
                <div>Gedung 47, Jalan Tb Simatupang no.47 Tanjung Barat Jagakarsa 12530</div>
                <div>T: 021 295 57233 &nbsp;E: secretariat@djakarta-miningclub.com / secretariat@coalclubindonesia.com</div>
            </td>
        </tr>
    </table>
    <hr class="header-divider">

    {{-- QUOTATION INFO + TITLE BOX --}}
    <table class="sub-header-table">
        <tr>
            <td class="meta-side">
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
            </td>
            <td class="title-box-side">
                <div class="orange-title-box" style="background-color: {{ $pkgColor }};">
                    <div class="title-main">RENEWAL FORM</div>
                    <div class="title-sub">DMC {{ $pkgDisplay }} SPONSORSHIP</div>
                </div>
            </td>
        </tr>
    </table>

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
                <td class="text-center val-middle font-bold">{{ $periodLabel }}</td>
                <td class="desc-cell">
                    <div class="desc-main-title">{{ $pkgDisplay }} SPONSOR OF DJAKARTA MINING CLUB</div>

                    @php $catIndex = 1; @endphp
                    @foreach($packageBenefits as $category => $items)
                        <div class="item-title">{{ $catIndex }}. {{ $category }}</div>
                        @foreach($items as $pb)
                            {{-- Semua info nempel di satu paragraf (bukan div terpisah per baris)
                                 biar teksnya wrap alami sesuai lebar kolom, sepadat referensi
                                 invoice lama — bukan dipaksa baris baru tiap ada catatan. --}}
                            <div class="item-sub">- {{ $pb->benefit->name }}
                                @if($pb->quantity > 1) ({{ $pb->quantity }}x)@endif
                                @if($pb->additional_info) ({{ $pb->additional_info }})@endif
                                @if($pb->benefit->description) ({{ $pb->benefit->description }})@endif
                            </div>
                        @endforeach
                        @php $catIndex++; @endphp
                    @endforeach
                </td>
                <td class="text-center val-middle font-bold">1 YEAR</td>
                <td class="val-middle font-bold">
                    <table class="currency-table"><tr>
                        @if($amountUsd)
                            <td>USD</td>
                            <td>{{ number_format($amountUsd, 0, '.', '.') }}</td>
                        @elseif($amountIdr)
                            <td>IDR</td>
                            <td>{{ number_format($amountIdr, 0, '.', '.') }}</td>
                        @else
                            <td>—</td><td></td>
                        @endif
                    </tr></table>
                </td>
            </tr>

            {{-- Totals rows --}}
            <tr>
                <td colspan="2" class="val-middle">
                    @if($kursRate)
                        <span style="display:inline-block;width:80px;">Rate:</span> IDR {{ number_format($kursRate, 0, '.', '.') }}
                    @endif
                </td>
                <td class="bg-dark val-middle">Total In USD</td>
                <td class="val-middle font-bold">
                    <table class="currency-table"><tr>
                        <td>USD</td>
                        <td>{{ $amountUsd ? number_format($amountUsd, 0, '.', '.') : '—' }}</td>
                    </tr></table>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="val-middle">
                    @if(!empty($renewalForm) && $renewalForm->kmk_number)
                        KMK Number {{ $renewalForm->kmk_number }}
                    @elseif($kursRate)
                        KMK Number {{ now()->format('Y') }}/MK/EF.2/{{ now()->format('Y') }}
                    @endif
                </td>
                <td class="bg-dark val-middle">Total In IDR</td>
                <td class="val-middle font-bold">
                    <table class="currency-table"><tr>
                        <td>IDR</td>
                        <td>
                            @if($amountIdr)
                                {{ number_format($amountIdr, 0, '.', '.') }}
                            @elseif($amountUsd && $kursRate)
                                {{ number_format($amountUsd * $kursRate, 0, '.', '.') }}
                            @else
                                —
                            @endif
                        </td>
                    </tr></table>
                </td>
            </tr>
            {{-- VAT/Grand Total — cuma muncul kalau sponsor ini emang dikenakan VAT
                 (vat_percent > 0 di renewal form-nya). Default 0% = baris ini nggak
                 tampil sama sekali, Total In USD/IDR di atas sudah final. --}}
            @if($vatPercent > 0)
            <tr>
                <td colspan="2" class="val-middle"></td>
                <td class="bg-dark val-middle">VAT {{ rtrim(rtrim(number_format($vatPercent, 2, '.', ''), '0'), '.') }}%</td>
                <td class="val-middle font-bold">
                    <table class="currency-table"><tr>
                        <td>IDR</td>
                        <td>{{ $vatAmountIdr !== null ? number_format($vatAmountIdr, 0, '.', '.') : '—' }}</td>
                    </tr></table>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="val-middle"></td>
                <td class="bg-dark val-middle">Grand Total IDR</td>
                <td class="val-middle font-bold">
                    <table class="currency-table"><tr>
                        <td>IDR</td>
                        <td>{{ $grandTotalIdr !== null ? number_format($grandTotalIdr, 0, '.', '.') : '—' }}</td>
                    </tr></table>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="val-middle"></td>
                <td class="bg-dark val-middle">Grand Total USD</td>
                <td class="val-middle font-bold">
                    <table class="currency-table"><tr>
                        <td>USD</td>
                        <td>{{ $grandTotalUsd !== null ? number_format($grandTotalUsd, 0, '.', '.') : '—' }}</td>
                    </tr></table>
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer-section">
        <div class="notes-block">
            <div class="notes-title">Notes:</div>
            <div>Confirmation no longer than 14 Day after Renewal form / Proposal Received</div>
            @if($vatPercent > 0)
                <div>Price is inclusive of {{ rtrim(rtrim(number_format($vatPercent, 2, '.', ''), '0'), '.') }}% VAT — see Grand Total</div>
            @else
                <div>Price are exclude VAT</div>
            @endif
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

<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .muted {
            color: #666;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        th {
            background: #f3f3f3;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="title">Financial Report - {{ $event->name }}</div>
    <div class="muted">
        Generated: {{ date('d M Y H:i') }}
        @if (!empty($filters['start_date']) || !empty($filters['end_date']))
            | Period: {{ $filters['start_date'] ?? '-' }} to {{ $filters['end_date'] ?? '-' }}
        @endif
        @if (!empty($filters['payment_method']))
            | Method: {{ $filters['payment_method'] }}
        @endif
    </div>

    <table style="margin-bottom:12px;">
        <tr>
            <th>Paid Transactions</th>
            <th>Gross</th>
            <th>Discount</th>
            <th>Net</th>
        </tr>
        <tr>
            <td>{{ $kpi->paid_trx ?? 0 }}</td>
            <td class="right">{{ number_format($kpi->gross_total ?? 0, 0, ',', '.') }}</td>
            <td class="right">{{ number_format($kpi->discount_total ?? 0, 0, ',', '.') }}</td>
            <td class="right"><b>{{ number_format($kpi->net_total ?? 0, 0, ',', '.') }}</b></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Paid At</th>
                <th>Code</th>
                <th>Buyer</th>
                <th>Company</th>
                <th>Ticket</th>
                <th>Method</th>
                <th class="right">Gross</th>
                <th class="right">Discount</th>
                <th class="right">Net</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ date('d M Y H:i', strtotime($r->paid_at)) }}</td>
                    <td>{{ $r->code_payment }}</td>
                    <td>{{ $r->name }} ({{ $r->email }})</td>
                    <td>{{ $r->company_name }}</td>
                    <td>{{ $r->ticket_title }}</td>
                    <td>{{ $r->payment_method }}</td>
                    <td class="right">{{ number_format($r->gross_amount ?? 0, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($r->discount ?? 0, 0, ',', '.') }}</td>
                    <td class="right"><b>{{ number_format($r->net_amount ?? 0, 0, ',', '.') }}</b></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

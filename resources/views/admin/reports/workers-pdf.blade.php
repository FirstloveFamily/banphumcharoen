<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <title>รายงานแรงงาน</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px
        }

        th {
            background: #f3f4f6
        }
    </style>
</head>

<body>
    <h2>รายงานแรงงาน</h2>
    <p>วันที่ออกรายงาน: {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>แรงงาน</th>
                <th>นายจ้าง</th>
                <th>passport</th>
                <th>WP</th>
                <th>Passport expiry</th>
                <th>WP expiry</th>
                <th>Visa expiry</th>
                <th>90 days</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($workers as $i => $w)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $w->full_name_th ?: $w->full_name_en }}</td>
                    <td>{{ $w->employer?->company_name ?? '-' }}</td>
                    <td>{{ $w->passport_number ?? '' }}</td>
                    <td>{{ $w->wp_number ?? '' }}</td>
                    <td>{{ $w->passport_expiry?->format('d/m/Y') ?? '' }}</td>
                    <td>{{ $w->wp_expiry?->format('d/m/Y') ?? '' }}</td>
                    <td>{{ $w->visa_expiry?->format('d/m/Y') ?? '' }}</td>
                    <td>{{ $w->report_90_days_due?->format('d/m/Y') ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

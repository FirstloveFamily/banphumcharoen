<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Financial Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
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
            background: #f3f4f6;
        }
    </style>
</head>

<body>
    <h2>รายงานการเงิน</h2>
    <table>
        <thead>
            <tr>
                <th>วันที่</th>
                <th>เลขที่งาน</th>
                <th>นายจ้าง</th>
                <th>แรงงาน</th>
                <th>จำนวน</th>
                <th>ช่องทาง</th>
                <th>อ้างอิง</th>
                <th>สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $p)
                <tr>
                    <td>{{ optional($p->payment_date)->format('Y-m-d') }}</td>
                    <td>{{ $p->jobOrder?->job_number ?? '-' }}</td>
                    <td>{{ $p->jobOrder?->employer?->company_name ?? '-' }}</td>
                    <td>{{ $p->jobOrder?->worker?->full_name_th ?? ($p->jobOrder?->worker?->full_name_en ?? '-') }}</td>
                    <td style="text-align:right">{{ number_format($p->amount, 2) }}</td>
                    <td>{{ $p->payment_method }}</td>
                    <td>{{ $p->payment_reference }}</td>
                    <td>{{ ucfirst($p->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

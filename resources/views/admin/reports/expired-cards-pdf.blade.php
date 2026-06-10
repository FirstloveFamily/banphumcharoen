<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <title>รายงานบัตรหมดอายุ</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
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
    <h2 style="margin-bottom:8px">รายงานบัตรหมดอายุ</h2>
    <p>วันที่ออกรายงาน: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>แรงงาน</th>
                <th>นายจ้าง</th>
                <th>ประเภทเอกสาร</th>
                <th>หมายเลขเอกสาร</th>
                <th>passport</th>
                <th>WP</th>
                <th>วันที่หมดอายุ</th>
                <th>วันเหลือก่อนหมด</th>
                <th>แหล่งที่มา</th>
                <th>หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $doc)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ is_array($doc) ? $doc['worker'] ?? '-' : $doc->worker?->full_name_th ?? 'Worker #' . $doc->worker_id }}
                    </td>
                    <td>{{ is_array($doc) ? $doc['employer'] ?? '-' : $doc->worker?->employer?->company_name ?? '-' }}
                    </td>
                    <td>{{ is_array($doc) ? $doc['document'] ?? '-' : $doc->documentMaster?->name ?? '-' }}</td>
                    <td>{{ is_array($doc) ? $doc['reference'] ?? '' : $doc->id ?? '' }}</td>
                    <td>{{ is_array($doc) ? $doc['passport_number'] ?? '' : $doc->worker?->passport_number ?? '' }}
                    </td>
                    <td>{{ is_array($doc) ? $doc['wp_number'] ?? '' : $doc->worker?->wp_number ?? '' }}</td>
                    <td>{{ is_array($doc) ? optional($doc['expiry_date'])->format('d/m/Y') ?? '-' : optional($doc->expiry_date)->format('d/m/Y') ?? '-' }}
                    </td>
                    <td>{{ is_array($doc) ? $doc['days_until_expiry'] ?? '' : (isset($doc->expiry_date) ? ($doc->expiry_date->isPast() ? -$doc->expiry_date->diffInDays(now()) : $doc->expiry_date->diffInDays(now())) : '') }}
                    </td>
                    <td>{{ is_array($doc) ? $doc['source'] ?? '' : 'document' }}</td>
                    <td>{{ is_array($doc) ? $doc['note'] ?? '' : $doc->note ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>

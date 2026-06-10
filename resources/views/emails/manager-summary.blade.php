<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0f172a; color: #ffffff; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background-color: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; border-radius: 0 0 10px 10px; }
        .stat-card { background-color: #ffffff; padding: 15px; margin-bottom: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; }
        .stat-label { font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .stat-value { font-size: 24px; font-weight: 800; color: #0f172a; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #94a3b8; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daily Business Summary</h1>
            <p>รายงานสรุปภาพรวมธุรกิจประจำวัน</p>
        </div>
        <div class="content">
            <p>สวัสดีครับคุณ Manager,</p>
            <p>นี่คือสรุปสถานะการดำเนินงานของระบบ <strong>บ้านพุ่มเจริญ</strong> ณ วันที่ {{ now()->format('d/m/Y') }}</p>

            <div class="stat-card">
                <div class="stat-label">ใบงานที่กำลังดำเนินการ (Active Pipeline)</div>
                <div class="stat-value">{{ number_format($summaryData['open_jobs']) }} งาน</div>
            </div>

            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="stat-label">รายการรออนุมัติ/ตรวจสลิป</div>
                <div class="stat-value">{{ number_format($summaryData['pending_reviews']) }} รายการ</div>
            </div>

            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="stat-label">ยอดรับชำระแล้ววันนี้</div>
                <div class="stat-value">฿{{ number_format($summaryData['revenue_today'], 2) }}</div>
            </div>

            <div class="stat-card" style="border-left-color: #ef4444;">
                <div class="stat-label">เอกสารหมดอายุ (ใน 45 วัน)</div>
                <div class="stat-value">{{ number_format($summaryData['expiring_docs']) }} รายการ</div>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/manager-dashboard" class="btn">เข้าสู่ระบบเพื่อจัดการงาน</a>
            </div>
        </div>
        <div class="footer">
            <p>ระบบแจ้งเตือนอัตโนมัติโดย Gemini CLI Engine</p>
            <p>&copy; {{ date('Y') }} บ้านพุ่มเจริญ จำกัด. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

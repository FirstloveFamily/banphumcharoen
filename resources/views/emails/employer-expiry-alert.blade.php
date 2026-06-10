<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 650px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1e3a8a; color: #ffffff; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background-color: #ffffff; padding: 30px; border: 1px solid #e2e8f0; border-radius: 0 0 10px 10px; }
        .alert-box { background-color: #fff7ed; border: 1px solid #fdba74; padding: 15px; border-radius: 8px; color: #9a3412; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; background-color: #f1f5f9; padding: 12px; font-size: 13px; color: #475569; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .expiry-date { color: #dc2626; font-weight: bold; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>การแจ้งเตือนวันหมดอายุเอกสาร</h2>
            <p>{{ $employer->company_name }}</p>
        </div>
        <div class="content">
            <p>เรียน คุณ {{ $employer->contact_name ?: 'ผู้ประกอบการ' }},</p>
            <p>ระบบ <strong>บ้านพุ่มเจริญ</strong> ตรวจพบว่ามีแรงงานในสังกัดของท่านที่มีเอกสารสำคัญหมดอายุแล้วหรือใกล้หมดอายุ (ภายใน 45 วัน) ดังนี้:</p>

            <div class="alert-box">
                <strong>คำแนะนำ:</strong> กรุณาเตรียมเอกสารและดำเนินการต่ออายุล่วงหน้า เพื่อป้องกันการทำผิดกฎหมายและค่าปรับที่อาจเกิดขึ้น
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ชื่อแรงงาน</th>
                        <th>ประเภทเอกสาร</th>
                        <th>วันหมดอายุ</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expiringWorkers as $worker)
                        @foreach($worker['docs'] as $doc)
                            <tr>
                                <td>{{ $worker['name'] }}</td>
                                <td>{{ $doc['type'] }}</td>
                                <td class="expiry-date">{{ $doc['expiry'] }}</td>
                                <td>{{ $doc['status'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <p style="margin-top: 30px;">หากท่านต้องการให้ทางบริษัทดำเนินการต่ออายุให้ หรือต้องการสอบถามข้อมูลเพิ่มเติม สามารถติดต่อเจ้าหน้าที่ของเราได้ทันทีครับ</p>
            
            <p>ขอแสดงความนับถือ,<br>ทีมงานฝ่ายบริการ - บ้านพุ่มเจริญ จำกัด</p>
        </div>
        <div class="footer">
            <p>นี่คืออีเมลอัตโนมัติจากระบบบริหารจัดการแรงงาน กรุณาอย่าตอบกลับอีเมลนี้</p>
        </div>
    </div>
</body>
</html>

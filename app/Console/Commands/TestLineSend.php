<?php

namespace App\Console\Commands;

use App\Services\LineMessagingService;
use Illuminate\Console\Command;

class TestLineSend extends Command
{
    protected $signature = 'line:test {user_id : LINE User ID to send test message to}';
    protected $description = 'Send a test message via LINE Messaging API';

    public function handle(LineMessagingService $lineService)
    {
        $userId = $this->argument('user_id');

        $this->info('📨 กำลังส่งข้อความทดสอบไปยัง: ' . $userId);

        $message = "✅ ทดสอบระบบ LINE Messaging API\n";
        $message .= "------------------------------\n";
        $message .= "📅 เวลา: " . now()->format('d/m/Y H:i:s') . "\n";
        $message .= "🏢 ระบบ: " . config('app.name') . "\n";
        $message .= "------------------------------\n";
        $message .= "หากคุณเห็นข้อความนี้ แสดงว่าระบบแจ้งเตือนทำงานปกติ 🎉";

        $result = $lineService->send($userId, $message);

        if ($result) {
            $this->info('✅ ส่งข้อความสำเร็จ!');
        } else {
            $this->error('❌ ส่งข้อความไม่สำเร็จ — ตรวจสอบ log ด้วย: php artisan pail หรือ storage/logs/laravel.log');
        }

        return $result ? 0 : 1;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LineWebhookController extends Controller
{
    /**
     * Handle LINE Webhook events
     * เมื่อมีคนส่งข้อความหา Bot จะบันทึก User ID ลง log
     */
    public function handle(Request $request)
    {
        $events = $request->input('events', []);

        foreach ($events as $event) {
            $userId = $event['source']['userId'] ?? null;
            $type = $event['type'] ?? 'unknown';

            if ($userId) {
                Log::channel('single')->info('🟢 LINE Webhook received', [
                    'event_type' => $type,
                    'user_id' => $userId,
                ]);

                // ถ้าเป็น follow event (เพิ่มเพื่อน) หรือ message event → อัพเดต user ถ้ามี
                if (in_array($type, ['follow', 'message'])) {
                    $user = \App\Models\User::whereNotNull('email')->first(); // จะปรับภายหลัง
                    Log::channel('single')->info("📌 LINE User ID: {$userId} — คัดลอกไปใส่ในหน้า Settings");
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}

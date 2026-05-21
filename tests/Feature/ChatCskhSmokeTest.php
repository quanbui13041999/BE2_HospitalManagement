<?php

namespace Tests\Feature;

use App\Models\ChatRoom;
use App\Models\User;
use App\Services\GeminiChatService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChatCskhSmokeTest extends TestCase
{
    public function test_patient_and_admin_can_use_chat_cskh_flow(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => env('DB_HOST', '127.0.0.1'),
            'database.connections.mysql.port' => env('DB_PORT', '3306'),
            'database.connections.mysql.database' => 'hospitalbookingdb',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->app->instance(GeminiChatService::class, new class extends GeminiChatService {
            public function __construct()
            {
            }

            public function generateReply(int $roomId, string $userMessage): string
            {
                return 'AI test reply';
            }
        });

        $patient = User::create([
            'full_name' => 'Chat Test Patient',
            'email' => 'chat_test_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 3,
            'status' => 1,
        ]);

        $admin = User::where('role_id', 1)->firstOrFail();
        $roomId = null;

        try {
            $roomResponse = $this->actingAs($patient)
                ->postJson(route('chat.room'))
                ->assertOk()
                ->assertJson(['success' => true]);

            $roomId = $roomResponse->json('room_id');

            $this->actingAs($patient)
                ->postJson(route('chat.send'), [
                    'room_id' => $roomId,
                    'message_text' => 'Xin chào CSKH',
                ])
                ->assertOk()
                ->assertJson(['success' => true]);

            $this->actingAs($patient)
                ->getJson(route('chat.messages', $roomId))
                ->assertOk()
                ->assertJson(['success' => true])
                ->assertJsonCount(2, 'messages');

            $this->actingAs($admin)
                ->getJson(route('admin.chatroom.list'))
                ->assertOk()
                ->assertJson(['success' => true]);

            $this->actingAs($admin)
                ->postJson(route('admin.chatroom.send', $roomId), [
                    'message_text' => 'CSKH đã nhận được tin nhắn',
                ])
                ->assertOk()
                ->assertJson(['success' => true]);

            $this->actingAs($admin)
                ->getJson(route('admin.chatroom.messages', $roomId))
                ->assertOk()
                ->assertJson(['success' => true])
                ->assertJsonCount(3, 'messages');

            $this->actingAs($admin)
                ->postJson(route('admin.chatroom.close', $roomId))
                ->assertOk()
                ->assertJson(['success' => true]);

            $this->assertSame('Đóng', ChatRoom::find($roomId)?->status);
        } finally {
            if ($roomId) {
                ChatRoom::where('room_id', $roomId)->delete();
            }

            $patient->delete();
        }
    }
}

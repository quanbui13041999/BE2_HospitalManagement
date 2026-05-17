<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\GeminiChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChatCskhSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_and_admin_can_use_chat_cskh_flow(): void
    {
        $this->app->instance(GeminiChatService::class, new class extends GeminiChatService {
            public function __construct()
            {
            }

            public function generateReply(int $roomId, string $userMessage): string
            {
                return 'AI test reply';
            }
        });

        Role::create(['role_name' => 'Admin']);
        Role::create(['role_name' => 'Doctor']);
        Role::create(['role_name' => 'Patient']);

        $patient = User::create([
            'full_name' => 'Chat Test Patient',
            'email' => 'chat_test_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 3,
            'status' => 1,
        ]);

        $admin = User::create([
            'full_name' => 'Chat Test Admin',
            'email' => 'chat_admin_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 1,
            'status' => 1,
        ]);
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

        $this->assertDatabaseHas('chatrooms', [
            'room_id' => $roomId,
            'status' => 'Đóng',
        ]);
    }
}

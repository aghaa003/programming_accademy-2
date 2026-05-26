<?php

namespace Tests\Feature;

use App\Http\Controllers\AiChatController;
use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Tests for the persistent AI conversation system (Qwen model via Ollama).
 *
 * The callOllamaChat method is mocked in every test that would otherwise
 * make a real HTTP call, so the suite runs without a running Ollama server.
 */
class AiConversationTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    private function mockQwen(array $return = []): void
    {
        $response = $return ?: ['success' => true, 'content' => 'رد الذكاء الاصطناعي'];
        $this->partialMock(AiChatController::class, function ($mock) use ($response) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('callOllamaChat')
                ->andReturn($response);
        });
    }

    /* ── Authentication guards ─────────────────────────────────────────────── */

    public function test_list_conversations_requires_auth(): void
    {
        $this->getJson('/api/ai/conversations')->assertStatus(401);
    }

    public function test_create_conversation_requires_auth(): void
    {
        $this->postJson('/api/ai/conversations')->assertStatus(401);
    }

    public function test_send_message_requires_auth(): void
    {
        $this->postJson('/api/ai/conversations/1/messages', ['message' => 'مرحبا'])
            ->assertStatus(401);
    }

    public function test_delete_conversation_requires_auth(): void
    {
        $this->deleteJson('/api/ai/conversations/1')->assertStatus(401);
    }

    /* ── Conversation CRUD ─────────────────────────────────────────────────── */

    public function test_create_conversation(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)->postJson('/api/ai/conversations');

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'title', 'user_id', 'created_at', 'updated_at']);

        $this->assertDatabaseHas('ai_conversations', ['user_id' => $user->id]);
    }

    public function test_list_conversations_returns_only_current_users(): void
    {
        $user1 = $this->user();
        $user2 = $this->user();

        AiConversation::create(['user_id' => $user1->id, 'title' => 'محادثة المستخدم الأول']);
        AiConversation::create(['user_id' => $user2->id, 'title' => 'محادثة المستخدم الثاني']);

        $response = $this->actingAs($user1)->getJson('/api/ai/conversations');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('*'));
        $this->assertEquals('محادثة المستخدم الأول', $response->json('0.title'));
    }

    public function test_show_conversation_returns_messages(): void
    {
        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => 'اختبار']);
        $conv->messages()->create(['role' => 'user',      'content' => 'مرحبا',   'has_images' => false]);
        $conv->messages()->create(['role' => 'assistant', 'content' => 'أهلاً!',  'has_images' => false]);

        $response = $this->actingAs($user)->getJson("/api/ai/conversations/{$conv->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'messages');
    }

    public function test_cannot_access_another_users_conversation(): void
    {
        $user1 = $this->user();
        $user2 = $this->user();
        $conv = AiConversation::create(['user_id' => $user1->id, 'title' => 'خاصة']);

        $this->actingAs($user2)->getJson("/api/ai/conversations/{$conv->id}")->assertStatus(404);
        $this->actingAs($user2)->deleteJson("/api/ai/conversations/{$conv->id}")->assertStatus(404);
    }

    public function test_delete_conversation(): void
    {
        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => 'تُحذف']);

        $this->actingAs($user)->deleteJson("/api/ai/conversations/{$conv->id}")
            ->assertStatus(200)
            ->assertJson(['message' => 'Conversation deleted']);

        $this->assertDatabaseMissing('ai_conversations', ['id' => $conv->id]);
    }

    public function test_rename_conversation(): void
    {
        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => 'قديم']);

        $this->actingAs($user)
            ->patchJson("/api/ai/conversations/{$conv->id}/title", ['title' => 'عنوان جديد'])
            ->assertStatus(200)
            ->assertJsonPath('title', 'عنوان جديد');

        $this->assertDatabaseHas('ai_conversations', ['id' => $conv->id, 'title' => 'عنوان جديد']);
    }

    public function test_rename_conversation_rejects_empty_title(): void
    {
        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => 'موجود']);

        $this->actingAs($user)
            ->patchJson("/api/ai/conversations/{$conv->id}/title", ['title' => ''])
            ->assertStatus(422)
            ->assertJsonStructure(['error']);
    }

    /* ── Input validation for sendMessage ─────────────────────────────────── */

    public function test_send_message_rejects_empty_message(): void
    {
        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => null]);

        $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", ['message' => ''])
            ->assertStatus(400)
            ->assertJsonStructure(['error']);
    }

    public function test_send_message_rejects_oversized_message(): void
    {
        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => null]);

        $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", [
                'message' => str_repeat('أ', 2001),
            ])
            ->assertStatus(413)
            ->assertJsonStructure(['error']);
    }

    public function test_send_message_to_nonexistent_conversation_returns_404(): void
    {
        $this->actingAs($this->user())
            ->postJson('/api/ai/conversations/99999/messages', ['message' => 'مرحبا'])
            ->assertStatus(404);
    }

    /* ── Qwen AI response ──────────────────────────────────────────────────── */

    public function test_send_message_returns_qwen_ai_response(): void
    {
        $this->mockQwen(['success' => true, 'content' => 'PHP هي لغة سكريبتية من جانب الخادم.']);

        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => null]);

        $response = $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", [
                'message' => 'ما هو الـ PHP؟',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('response', 'PHP هي لغة سكريبتية من جانب الخادم.')
            ->assertJsonStructure(['success', 'response', 'title', 'message_id']);

        // Both messages persisted to DB
        $this->assertDatabaseHas('ai_messages', [
            'conversation_id' => $conv->id,
            'role' => 'user',
            'content' => 'ما هو الـ PHP؟',
        ]);
        $this->assertDatabaseHas('ai_messages', [
            'conversation_id' => $conv->id,
            'role' => 'assistant',
            'content' => 'PHP هي لغة سكريبتية من جانب الخادم.',
        ]);
    }

    public function test_first_message_auto_sets_conversation_title(): void
    {
        $this->mockQwen(['success' => true, 'content' => 'إجابة']);

        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => null]);

        $response = $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", [
                'message' => 'شرح مفهوم التوارث في البرمجة',
            ]);

        $response->assertStatus(200);

        // Title is auto-set from the first message text
        $this->assertDatabaseHas('ai_conversations', [
            'id' => $conv->id,
            'title' => 'شرح مفهوم التوارث في البرمجة',
        ]);
        $this->assertEquals('شرح مفهوم التوارث في البرمجة', $response->json('title'));
    }

    public function test_subsequent_messages_do_not_overwrite_title(): void
    {
        $this->mockQwen(['success' => true, 'content' => 'رد ثاني']);

        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => 'عنوان محدد مسبقاً']);

        $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", [
                'message' => 'رسالة ثانية',
            ])
            ->assertStatus(200);

        // Title must not have changed
        $this->assertDatabaseHas('ai_conversations', [
            'id' => $conv->id,
            'title' => 'عنوان محدد مسبقاً',
        ]);
    }

    public function test_send_message_returns_502_when_qwen_model_is_unavailable(): void
    {
        $this->mockQwen(['success' => false, 'error' => 'connection timeout']);

        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => null]);

        $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", [
                'message' => 'سؤال ما',
            ])
            ->assertStatus(502)
            ->assertJsonPath('success', false);
    }

    public function test_send_message_returns_500_when_qwen_returns_empty_content(): void
    {
        $this->mockQwen(['success' => true, 'content' => '']);

        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => null]);

        $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", [
                'message' => 'سؤال آخر',
            ])
            ->assertStatus(500)
            ->assertJsonPath('success', false);
    }

    public function test_conversation_history_is_sent_to_qwen_model(): void
    {
        // Capture what messages array is passed to callOllamaChat
        $capturedMessages = null;

        $this->partialMock(AiChatController::class, function ($mock) use (&$capturedMessages) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('callOllamaChat')
                ->once()
                ->withArgs(function (array $messages) use (&$capturedMessages) {
                    $capturedMessages = $messages;

                    return true;
                })
                ->andReturn(['success' => true, 'content' => 'رد']);
        });

        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => 'سياق']);
        $conv->messages()->create(['role' => 'user',      'content' => 'الرسالة الأولى',  'has_images' => false]);
        $conv->messages()->create(['role' => 'assistant', 'content' => 'الرد الأول',       'has_images' => false]);

        $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", [
                'message' => 'رسالة متابعة',
            ]);

        // System message + 2 history messages + new user message = 4 total
        $this->assertNotNull($capturedMessages);
        $this->assertGreaterThanOrEqual(4, count($capturedMessages));
        $this->assertEquals('system', $capturedMessages[0]['role']);
    }

    public function test_message_with_image_saves_has_images_and_uses_vision_model(): void
    {
        $capturedModel = null;

        $this->partialMock(AiChatController::class, function ($mock) use (&$capturedModel) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('callOllamaChat')
                ->once()
                ->withArgs(function (array $messages, int $timeout, float $temp, ?string $model, array $imgs) use (&$capturedModel) {
                    $capturedModel = $model;

                    return true;
                })
                ->andReturn(['success' => true, 'content' => 'أرى في الصورة...']);
        });

        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => null]);
        // fake()->create() works without the GD extension; fake()->image() requires GD
        $fakeImg = UploadedFile::fake()->create('photo.jpg', 10, 'image/jpeg');

        $response = $this->actingAs($user)
            ->post("/api/ai/conversations/{$conv->id}/messages", [
                'message' => 'ما هذه الصورة؟',
                'images' => [$fakeImg],
            ], ['Accept' => 'application/json']);

        $response->assertOk()->assertJsonPath('success', true);

        // has_images must be saved as true
        $this->assertDatabaseHas('ai_messages', [
            'conversation_id' => $conv->id,
            'role' => 'user',
            'has_images' => true,
        ]);

        // The vision model must have been chosen, not the text model
        $this->assertEquals(
            config('ai.ollama_vision_model', 'qwen3-vl:235b-cloud'),
            $capturedModel
        );
    }

    public function test_follow_up_text_message_in_image_conversation_still_uses_vision_model(): void
    {
        $capturedModel = null;

        $this->partialMock(AiChatController::class, function ($mock) use (&$capturedModel) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('callOllamaChat')
                ->once()
                ->withArgs(function (array $messages, int $timeout, float $temp, ?string $model, array $imgs) use (&$capturedModel) {
                    $capturedModel = $model;

                    return true;
                })
                ->andReturn(['success' => true, 'content' => 'رد نصي']);
        });

        $user = $this->user();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => 'محادثة صور']);

        // Seed a previous message that had images (simulates prior image upload)
        $conv->messages()->create(['role' => 'user',      'content' => 'ما هذه الصورة؟', 'has_images' => true]);
        $conv->messages()->create(['role' => 'assistant', 'content' => 'أرى...',           'has_images' => false]);

        // Now send a plain text follow-up — no images in this message
        $response = $this->actingAs($user)
            ->postJson("/api/ai/conversations/{$conv->id}/messages", [
                'message' => 'هل يمكنك التفصيل أكثر؟',
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        // Must still use vision model because conversation history has images
        $this->assertEquals(
            config('ai.ollama_vision_model', 'qwen3-vl:235b-cloud'),
            $capturedModel
        );
    }
}

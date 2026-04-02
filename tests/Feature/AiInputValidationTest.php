<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AiInputValidationTest extends TestCase
{
    private function actingAsUser(): static
    {
        // make() creates an in-memory user without persisting to DB — sufficient
        // for routes that only need a logged-in subject without querying user data.
        return $this->actingAs(User::factory()->make());
    }

    public function test_ai_general_requires_authentication(): void
    {
        $response = $this->postJson('/api/ai/helper', ['message' => 'hello']);
        $response->assertStatus(401);
    }

    public function test_ai_general_rejects_empty_message(): void
    {
        $response = $this->actingAsUser()->postJson('/api/ai/helper', ['message' => '']);
        $response->assertStatus(400)
                 ->assertJson(['success' => false]);
    }

    public function test_ai_general_rejects_oversized_message(): void
    {
        $response = $this->actingAsUser()->postJson('/api/ai/helper', [
            'message' => str_repeat('a', 2001),
        ]);
        $response->assertStatus(413)
                 ->assertJson(['success' => false]);
    }

    public function test_ai_general_rejects_oversized_code(): void
    {
        $response = $this->actingAsUser()->postJson('/api/ai/helper', [
            'message' => 'review this',
            'code'    => str_repeat('x', 8001),
        ]);
        $response->assertStatus(413)
                 ->assertJson(['success' => false]);
    }

    public function test_ai_challenges_requires_authentication(): void
    {
        $response = $this->postJson('/api/ai/helper-challenges', ['mode' => 'hint']);
        $response->assertStatus(401);
    }

    public function test_ai_projects_requires_authentication(): void
    {
        $response = $this->postJson('/api/ai/helper-projects', ['mode' => 'hint']);
        $response->assertStatus(401);
    }
}

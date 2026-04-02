<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    /**
     * Unauthenticated requests to all admin endpoints must be rejected
     * with 401 before any business logic or DB queries run.
     */
    public function test_admin_stats_requires_authentication(): void
    {
        $this->getJson('/api/admin/stats')->assertStatus(401);
    }

    public function test_admin_courses_list_requires_authentication(): void
    {
        $this->getJson('/api/admin/courses')->assertStatus(401);
    }

    public function test_admin_create_course_requires_authentication(): void
    {
        $this->postJson('/api/admin/courses', ['title' => 'Test'])->assertStatus(401);
    }

    public function test_admin_challenges_requires_authentication(): void
    {
        $this->getJson('/api/admin/challenges')->assertStatus(401);
    }

    public function test_admin_users_requires_authentication(): void
    {
        $this->getJson('/api/admin/users')->assertStatus(401);
    }

    public function test_admin_delete_user_requires_authentication(): void
    {
        $this->deleteJson('/api/admin/users/1')->assertStatus(401);
    }
}

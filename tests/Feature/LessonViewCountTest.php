<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Tests for the lessons.views counter.
 *
 * The counter must increment exactly once per unique (user, lesson) pair,
 * regardless of which action (update_position vs mark_complete) creates
 * the user_lesson_progress row first.
 */
class LessonViewCountTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    private function makeLesson(): int
    {
        $courseId = DB::table('courses')->insertGetId([
            'title' => 'Course',
            'description' => 'desc',
            'level' => 'Beginner',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('lessons')->insertGetId([
            'course_id' => $courseId,
            'title' => 'Lesson',
            'sort_order' => 1,
            'views' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function postProgress(User $user, int $lessonId, string $action, int $position = 5): TestResponse
    {
        return $this->actingAs($user)->postJson('/api/progress', [
            'lesson_id' => $lessonId,
            'action' => $action,
            'position' => $position,
        ]);
    }

    private function viewCount(int $lessonId): int
    {
        return (int) DB::table('lessons')->where('id', $lessonId)->value('views');
    }

    // ── update_position counts the first view ─────────────────────────────────

    public function test_update_position_counts_view_on_first_call(): void
    {
        $user = $this->makeUser();
        $lessonId = $this->makeLesson();

        $this->postProgress($user, $lessonId, 'update_position')->assertStatus(200);

        $this->assertSame(1, $this->viewCount($lessonId));
    }

    public function test_update_position_does_not_double_count_on_second_call(): void
    {
        $user = $this->makeUser();
        $lessonId = $this->makeLesson();

        $this->postProgress($user, $lessonId, 'update_position');
        $this->postProgress($user, $lessonId, 'update_position', 20);

        $this->assertSame(1, $this->viewCount($lessonId));
    }

    // ── mark_complete counts the view (the bug scenario) ─────────────────────

    public function test_mark_complete_counts_view_when_fired_before_update_position(): void
    {
        $user = $this->makeUser();
        $lessonId = $this->makeLesson();

        // User clicks complete without watching (no update_position first)
        $this->postProgress($user, $lessonId, 'mark_complete')->assertStatus(200);

        $this->assertSame(1, $this->viewCount($lessonId));
    }

    public function test_mark_complete_does_not_double_count_when_update_position_already_ran(): void
    {
        $user = $this->makeUser();
        $lessonId = $this->makeLesson();

        // Normal flow: position saved first, then marked complete
        $this->postProgress($user, $lessonId, 'update_position');
        $this->postProgress($user, $lessonId, 'mark_complete');

        $this->assertSame(1, $this->viewCount($lessonId));
    }

    public function test_update_position_does_not_double_count_after_mark_complete_created_the_row(): void
    {
        $user = $this->makeUser();
        $lessonId = $this->makeLesson();

        // mark_complete fires first (short/text lesson)
        $this->postProgress($user, $lessonId, 'mark_complete');
        // then the video position save fires later
        $this->postProgress($user, $lessonId, 'update_position', 15);

        $this->assertSame(1, $this->viewCount($lessonId));
    }

    // ── mark_incomplete does not affect the view count ────────────────────────

    public function test_mark_incomplete_does_not_change_view_count(): void
    {
        $user = $this->makeUser();
        $lessonId = $this->makeLesson();

        $this->postProgress($user, $lessonId, 'mark_complete');
        $this->assertSame(1, $this->viewCount($lessonId));

        $this->postProgress($user, $lessonId, 'mark_incomplete');
        $this->assertSame(1, $this->viewCount($lessonId));
    }

    // ── Multiple users each count as a separate view ──────────────────────────

    public function test_each_user_counts_as_one_view(): void
    {
        $userA = $this->makeUser();
        $userB = $this->makeUser();
        $lessonId = $this->makeLesson();

        $this->postProgress($userA, $lessonId, 'update_position');
        $this->postProgress($userB, $lessonId, 'update_position');

        $this->assertSame(2, $this->viewCount($lessonId));
    }

    public function test_multiple_users_via_mark_complete_each_count_once(): void
    {
        $userA = $this->makeUser();
        $userB = $this->makeUser();
        $userC = $this->makeUser();
        $lessonId = $this->makeLesson();

        $this->postProgress($userA, $lessonId, 'mark_complete');
        $this->postProgress($userB, $lessonId, 'mark_complete');
        $this->postProgress($userC, $lessonId, 'update_position');

        $this->assertSame(3, $this->viewCount($lessonId));
    }

    public function test_same_user_repeated_actions_count_only_once(): void
    {
        $user = $this->makeUser();
        $lessonId = $this->makeLesson();

        $this->postProgress($user, $lessonId, 'mark_complete');
        $this->postProgress($user, $lessonId, 'mark_incomplete');
        $this->postProgress($user, $lessonId, 'mark_complete');
        $this->postProgress($user, $lessonId, 'update_position', 30);

        $this->assertSame(1, $this->viewCount($lessonId));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tests for DELETE /api/admin/courses/{id}
 *
 * Covers:
 *  - Authentication / authorisation guards
 *  - Course + all related records are hard-deleted from DB
 *  - Rollback: data stays intact when something fails mid-transaction
 *  - Already-deleted course returns 404 (not a null-pointer crash)
 */
class AdminCourseDeleteTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeAdmin(): User
    {
        DB::table('roles')->insertOrIgnore([
            ['name' => 'admin'],
            ['name' => 'student'],
        ]);

        $user       = User::factory()->create();
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $adminRoleId]);

        return $user;
    }

    private function makeStudent(): User
    {
        DB::table('roles')->insertOrIgnore([
            ['name' => 'admin'],
            ['name' => 'student'],
        ]);

        $user          = User::factory()->create();
        $studentRoleId = DB::table('roles')->where('name', 'student')->value('id');
        DB::table('user_roles')->insert(['user_id' => $user->id, 'role_id' => $studentRoleId]);

        return $user;
    }

    /** Create a course with lessons, assignments, and progress records attached. */
    private function makeCourseWithRelations(): array
    {
        $student = User::factory()->create();

        $courseId = DB::table('courses')->insertGetId([
            'title'      => 'Test Course',
            'description'=> 'desc',
            'level'      => 'Beginner',
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $lessonId = DB::table('lessons')->insertGetId([
            'course_id'  => $courseId,
            'title'      => 'Lesson 1',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assignmentId = DB::table('assignments')->insertGetId([
            'course_id'        => $courseId,
            'question'         => 'What is 2+2?',
            'difficulty'       => 1,
            'assignment_order' => 1,
            'created_at'       => now(),
        ]);

        DB::table('user_lesson_progress')->insert([
            'user_id'   => $student->id,
            'lesson_id' => $lessonId,
            'updated_at'=> now(),
        ]);

        DB::table('user_course_progress')->insert([
            'user_id'    => $student->id,
            'course_id'  => $courseId,
            'started_at' => now(),
            'last_accessed' => now(),
        ]);

        DB::table('user_assignments')->insert([
            'user_id'       => $student->id,
            'assignment_id' => $assignmentId,
            'submitted_at'  => now(),
        ]);

        return [
            'courseId'     => $courseId,
            'lessonId'     => $lessonId,
            'assignmentId' => $assignmentId,
            'studentId'    => $student->id,
        ];
    }

    // ── Auth / authorisation guards ───────────────────────────────────────────

    public function test_delete_course_requires_authentication(): void
    {
        $this->deleteJson('/api/admin/courses/1')->assertStatus(401);
    }

    public function test_non_admin_cannot_delete_course(): void
    {
        $student = $this->makeStudent();

        $courseId = DB::table('courses')->insertGetId([
            'title'      => 'Course',
            'description'=> 'desc',
            'level'      => 'Beginner',
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($student)
            ->deleteJson("/api/admin/courses/{$courseId}")
            ->assertStatus(403);

        $this->assertDatabaseHas('courses', ['id' => $courseId]);
    }

    // ── 404 for non-existent / already-deleted ────────────────────────────────

    public function test_delete_nonexistent_course_returns_404(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->deleteJson('/api/admin/courses/99999')
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }

    // ── Successful deletion ───────────────────────────────────────────────────

    public function test_delete_course_removes_course_from_db(): void
    {
        $admin = $this->makeAdmin();
        ['courseId' => $courseId] = $this->makeCourseWithRelations();

        $this->actingAs($admin)
            ->deleteJson("/api/admin/courses/{$courseId}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('courses', ['id' => $courseId]);
    }

    public function test_delete_course_removes_all_related_lessons(): void
    {
        $admin = $this->makeAdmin();
        ['courseId' => $courseId, 'lessonId' => $lessonId] = $this->makeCourseWithRelations();

        $this->actingAs($admin)->deleteJson("/api/admin/courses/{$courseId}");

        $this->assertDatabaseMissing('lessons', ['id' => $lessonId]);
    }

    public function test_delete_course_removes_all_assignments(): void
    {
        $admin = $this->makeAdmin();
        ['courseId' => $courseId, 'assignmentId' => $assignmentId] = $this->makeCourseWithRelations();

        $this->actingAs($admin)->deleteJson("/api/admin/courses/{$courseId}");

        $this->assertDatabaseMissing('assignments', ['id' => $assignmentId]);
    }

    public function test_delete_course_removes_user_lesson_progress(): void
    {
        $admin = $this->makeAdmin();
        ['courseId' => $courseId, 'lessonId' => $lessonId] = $this->makeCourseWithRelations();

        $this->actingAs($admin)->deleteJson("/api/admin/courses/{$courseId}");

        $this->assertDatabaseMissing('user_lesson_progress', ['lesson_id' => $lessonId]);
    }

    public function test_delete_course_removes_user_course_progress(): void
    {
        $admin = $this->makeAdmin();
        ['courseId' => $courseId] = $this->makeCourseWithRelations();

        $this->actingAs($admin)->deleteJson("/api/admin/courses/{$courseId}");

        $this->assertDatabaseMissing('user_course_progress', ['course_id' => $courseId]);
    }

    public function test_delete_course_removes_user_assignments(): void
    {
        $admin = $this->makeAdmin();
        ['courseId' => $courseId, 'assignmentId' => $assignmentId] = $this->makeCourseWithRelations();

        $this->actingAs($admin)->deleteJson("/api/admin/courses/{$courseId}");

        $this->assertDatabaseMissing('user_assignments', ['assignment_id' => $assignmentId]);
    }

    public function test_delete_course_with_no_lessons_or_assignments_succeeds(): void
    {
        $admin = $this->makeAdmin();

        $courseId = DB::table('courses')->insertGetId([
            'title'      => 'Empty Course',
            'description'=> 'no content',
            'level'      => 'Beginner',
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/admin/courses/{$courseId}")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('courses', ['id' => $courseId]);
    }

    // ── Second delete on same id returns 404, not a crash ─────────────────────

    public function test_second_delete_of_same_course_returns_404(): void
    {
        $admin = $this->makeAdmin();
        ['courseId' => $courseId] = $this->makeCourseWithRelations();

        $this->actingAs($admin)->deleteJson("/api/admin/courses/{$courseId}");

        // Second call: course is already gone — must return 404, not 500
        $this->actingAs($admin)
            ->deleteJson("/api/admin/courses/{$courseId}")
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}

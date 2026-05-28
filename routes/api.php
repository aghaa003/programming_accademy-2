<?php

use App\Http\Controllers\Admin\AdminAssignmentController;
use App\Http\Controllers\Admin\AdminChallengeController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminPlatformController;
use App\Http\Controllers\Admin\AdminUploadController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\CommunityCommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ExampleController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\LessonCommentController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\LessonLikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PathController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\VideoStreamController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ROUTES (no auth required)
// ============================================================

// Auth
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});
Route::middleware('throttle:20,1')->post('/check-availability', [AuthController::class, 'checkAvailability']);
Route::middleware('throttle:5,1')->group(function () {
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Public listings (rate-limited: 100 requests per minute per IP)
Route::middleware('throttle:100,1')->group(function () {
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::get('/courses/{courseId}/reviews', [ReviewController::class, 'courseIndex']);
    Route::get('/platforms', [PlatformController::class, 'index']);
    Route::get('/challenges', [ChallengeController::class, 'index']);
    Route::get('/challenge-stats', [ChallengeController::class, 'stats']);
    Route::get('/user-challenge-progress', [ChallengeController::class, 'userProgress']);
    Route::get('/examples', [ExampleController::class, 'index']);
    Route::get('/examples/{id}', [ExampleController::class, 'show']);
    Route::get('/leaderboard', [LeaderboardController::class, 'index']);

    // Paths
    Route::get('/paths', [PathController::class, 'index']);
    Route::get('/paths/{path}', [PathController::class, 'show']);

    // Platform stats / recommendations
    Route::get('/platform-stats', [PlatformController::class, 'stats']);
    Route::get('/platform-recommendations', [PlatformController::class, 'recommendations']);

    // Reviews (GET is public, POST requires auth)
    Route::get('/home-reviews', [ReviewController::class, 'index']);

    // Public avatar endpoint for profile/review images
    Route::get('/avatar/{userId}', [ProfileController::class, 'getAvatar']);

    // Courses with assignments (public - category filter)
    Route::get('/courses-with-assignments', [AssignmentController::class, 'coursesWithAssignments']);
});

// Public user profile endpoint
Route::get('/users/{userId}', [ProfileController::class, 'publicProfile']);

// ============================================================
// AUTHENTICATED ROUTES (Sanctum SPA auth)
// ============================================================

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [ProfileController::class, 'show']);

    // User status
    Route::get('/user/status', [ProfileController::class, 'status']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/users/profile', [ProfileController::class, 'show']);
    Route::middleware('throttle:15,1')->group(function () {
        Route::post('/profile', [ProfileController::class, 'update']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/users/profile', [ProfileController::class, 'update']);
        Route::delete('/profile', [ProfileController::class, 'destroy']);
        Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar']);
        Route::post('/update-language', [ProfileController::class, 'updateLanguage']);
        Route::post('/save-user-preferences', [ProfileController::class, 'savePreferences']);
    });

    // Courses (user-specific)
    Route::get('/user-courses', [CourseController::class, 'userCourses']);
    Route::get('/courses/{courseId}/viewers', [CourseController::class, 'viewers']);
    Route::delete('/course-progress/{courseId}', [CourseController::class, 'deleteCourseProgress']);

    // Lessons
    Route::get('/lessons', [LessonController::class, 'index']);

    // Progress
    Route::middleware('throttle:60,1')->post('/progress', [ProgressController::class, 'update']);
    Route::get('/user-progress', [ProgressController::class, 'userProgress']);

    // Video streaming
    Route::get('/stream-video', [VideoStreamController::class, 'stream']);

    // Platform interactions
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/toggle-bookmark', [PlatformController::class, 'toggleBookmark']);
        Route::post('/rate-platform', [PlatformController::class, 'ratePlatform']);
    });

    // Reviews
    Route::middleware('throttle:20,1')->post('/courses/{courseId}/reviews', [ReviewController::class, 'storeForCourse']);
    Route::middleware('throttle:20,1')->post('/reviews', [ReviewController::class, 'store']);

    // Challenges (rate-limited: max 30 submissions per minute)
    Route::middleware('throttle:30,1')->post('/challenges/submit', [ChallengeController::class, 'submit']);

    // Assignments
    Route::get('/assignments/review', [AssignmentController::class, 'review']);
    Route::get('/assignments', [AssignmentController::class, 'index']);
    Route::middleware('throttle:20,1')->post('/assignments/submit', [AssignmentController::class, 'submit']);

    // Repositories (rate-limited: 30 per minute for submissions/likes)
    Route::middleware('throttle:100,1')->group(function () {
        Route::get('/repositories', [RepositoryController::class, 'index']);
        Route::get('/repositories/{id}', [RepositoryController::class, 'show']);
    });
    Route::middleware('throttle:20,1')->post('/repositories', [RepositoryController::class, 'store']);
    Route::middleware('throttle:30,1')->group(function () {
        Route::put('/repositories/{id}', [RepositoryController::class, 'update']);
        Route::delete('/repositories/{id}', [RepositoryController::class, 'destroy']);
        Route::post('/repositories/{id}/like', [RepositoryController::class, 'like']);
    });

    // Community Posts (rate-limited: 20 per minute for writes)
    Route::middleware('throttle:100,1')->group(function () {
        Route::get('/community/posts', [CommunityController::class, 'index']);
        Route::get('/community/posts/{id}', [CommunityController::class, 'show']);
    });
    Route::middleware('throttle:20,1')->post('/community/posts', [CommunityController::class, 'store']);
    Route::middleware('throttle:30,1')->group(function () {
        Route::put('/community/posts/{id}', [CommunityController::class, 'update']);
        Route::delete('/community/posts/{id}', [CommunityController::class, 'destroy']);
        Route::post('/community/posts/{id}/like', [CommunityController::class, 'like']);
    });

    // Community Comments (rate-limited: 30 per minute)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/community/posts/{postId}/comments', [CommunityCommentController::class, 'store']);
        Route::put('/community/posts/{postId}/comments/{commentId}', [CommunityCommentController::class, 'update']);
        Route::delete('/community/posts/{postId}/comments/{commentId}', [CommunityCommentController::class, 'destroy']);
    });

    // Lesson Comments (rate-limited: 30 per minute)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/lessons/{lessonId}/comments', [LessonCommentController::class, 'store']);
        Route::put('/lessons/{lessonId}/comments/{commentId}', [LessonCommentController::class, 'update']);
        Route::delete('/lessons/{lessonId}/comments/{commentId}', [LessonCommentController::class, 'destroy']);
    });

    // Lesson Likes (rate-limited: 30 per minute)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/lessons/{lessonId}/like', [LessonLikeController::class, 'toggle']);
        Route::get('/lessons/{lessonId}/likes', [LessonLikeController::class, 'getLikes']);
    });

    // Notifications (rate-limited: 100 per minute for reads)
    Route::middleware('throttle:100,1')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    });
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'delete']);
        Route::delete('/notifications', [NotificationController::class, 'deleteAll']);
    });

    // File Uploads (rate-limited: 20 per minute, 50MB max per file)
    Route::middleware('throttle:20,1')->post('/uploads', [UploadController::class, 'store']);
    Route::middleware('throttle:30,1')->group(function () {
        Route::delete('/uploads/{id}', [UploadController::class, 'destroy']);
        Route::get('/uploads', [UploadController::class, 'getUserUploads']);
    });

    // AI — per-endpoint limits tuned to Ollama cost per call
    Route::middleware('throttle:10,1')->post('/ai/helper', [AiController::class, 'general']);           // general chat: lightweight, 10/min
    Route::middleware('throttle:5,1')->post('/ai/helper-challenges', [AiController::class, 'challenges']); // verify/solution: up to 60s, 5/min
    Route::middleware('throttle:5,1')->post('/ai/helper-projects', [AiController::class, 'projects']);     // fix/code-check: similarly heavy, 5/min

    // AI persistent conversations
    Route::middleware('throttle:20,1')->group(function () {
        Route::get('/ai/conversations', [AiChatController::class, 'index']);
        Route::post('/ai/conversations', [AiChatController::class, 'store']);
        Route::get('/ai/conversations/{id}', [AiChatController::class, 'show']);
        Route::delete('/ai/conversations/{id}', [AiChatController::class, 'destroy']);
        Route::patch('/ai/conversations/{id}/title', [AiChatController::class, 'rename']);
    });
    Route::middleware('throttle:10,1')->post('/ai/conversations/{id}/messages', [AiChatController::class, 'sendMessage']);
});

// ============================================================
// ADMIN ROUTES (Sanctum SPA auth + admin role)
// ============================================================

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Stats
    Route::get('/stats', [AdminController::class, 'stats']);
    Route::get('/audit-logs', [AdminController::class, 'auditLogs']);
    Route::get('/logs', [AdminController::class, 'auditLogs']);  // Alias for frontend
    Route::get('/engagements', [AdminController::class, 'engagements']);
    Route::get('/comments', [AdminController::class, 'comments']);
    Route::get('/reviews', [AdminController::class, 'reviews']);
    Route::post('/reviews/{id}/approve', [AdminController::class, 'approveReview']);
    Route::post('/reviews/{id}/reject', [AdminController::class, 'rejectReview']);

    // Courses & Lessons
    Route::get('/courses', [AdminCourseController::class, 'index']);
    Route::post('/courses', [AdminCourseController::class, 'store']);
    Route::get('/courses/{id}', [AdminCourseController::class, 'show']);
    Route::put('/courses/{id}', [AdminCourseController::class, 'update']);
    Route::delete('/courses/{id}', [AdminCourseController::class, 'destroy']);
    Route::get('/courses/{id}/lessons', [AdminCourseController::class, 'lessons']);
    Route::post('/courses/{id}/upload-logo', [AdminCourseController::class, 'uploadLogo']);
    Route::delete('/courses/{id}/logo', [AdminCourseController::class, 'deleteLogo']);

    Route::post('/lessons', [AdminCourseController::class, 'storeLesson']);
    Route::put('/lessons/{id}', [AdminCourseController::class, 'updateLesson']);
    Route::delete('/lessons/{id}', [AdminCourseController::class, 'destroyLesson']);
    Route::post('/lessons/{id}/reorder', [AdminCourseController::class, 'reorderLesson']);

    // Platforms & Examples
    Route::get('/platforms', [AdminPlatformController::class, 'index']);
    Route::post('/platforms', [AdminPlatformController::class, 'store']);
    Route::post('/platforms/upload-logo', [AdminPlatformController::class, 'uploadLogo']);
    Route::get('/platforms/{id}', [AdminPlatformController::class, 'show']);
    Route::put('/platforms/{id}', [AdminPlatformController::class, 'update']);
    Route::delete('/platforms/{id}', [AdminPlatformController::class, 'destroy']);

    Route::get('/examples', [AdminPlatformController::class, 'examples']);
    Route::post('/examples', [AdminPlatformController::class, 'storeExample']);
    Route::get('/examples/{id}', [AdminPlatformController::class, 'showExample']);
    Route::put('/examples/{id}', [AdminPlatformController::class, 'updateExample']);
    Route::delete('/examples/{id}', [AdminPlatformController::class, 'destroyExample']);

    // Challenges
    Route::get('/challenges', [AdminChallengeController::class, 'index']);
    Route::post('/challenges', [AdminChallengeController::class, 'store']);
    Route::get('/challenges/{id}', [AdminChallengeController::class, 'show']);
    Route::put('/challenges/{id}', [AdminChallengeController::class, 'update']);
    Route::delete('/challenges/{id}', [AdminChallengeController::class, 'destroy']);
    Route::post('/challenges/{challengeId}/grade-user/{userId}', [AdminChallengeController::class, 'gradeUser']);

    // Assignments
    Route::get('/assignments', [AdminAssignmentController::class, 'index']);
    Route::post('/assignments', [AdminAssignmentController::class, 'store']);
    Route::get('/assignments/{id}', [AdminAssignmentController::class, 'show']);
    Route::put('/assignments/{id}', [AdminAssignmentController::class, 'update']);
    Route::delete('/assignments/{id}', [AdminAssignmentController::class, 'destroy']);
    Route::patch('/assignments/{id}/toggle', [AdminAssignmentController::class, 'toggle']);
    Route::get('/assignments/{id}/submissions', [AdminAssignmentController::class, 'submissions']);
    Route::post('/assignments/{submissionId}/grade', [AdminAssignmentController::class, 'grade']);

    // Upload (courses + lessons with videos)
    Route::post('/upload', [AdminUploadController::class, 'upload']);

    // Admin comment deletion
    Route::delete('/comments/{id}', [LessonCommentController::class, 'adminDestroy']);

    // Users
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::post('/users/{id}/toggle-admin', [AdminUserController::class, 'toggleAdmin']);
    Route::post('/users/{id}/toggle-suspend', [AdminUserController::class, 'toggleSuspend']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
});

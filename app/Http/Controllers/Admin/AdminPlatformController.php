<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminPlatformRequest;
use App\Models\Example;
use App\Models\Platform;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPlatformController extends Controller
{
    /** GET /api/admin/platforms */
    public function index(Request $request)
    {
        // M4: Paginate admin platform list
        $limit  = min((int) $request->query('limit', 20), 100);
        $offset = max((int) $request->query('offset', 0), 0);
        $total  = Platform::count();

        $platforms = Platform::withCount('bookmarks as bookmark_count')
            ->withCount('ratings as ratings_count')
            ->withAvg('ratings as avg_user_rating', 'rating')
            ->orderBy('id', 'desc')->skip($offset)->take($limit)->get();

        return response()->json(['success' => true, 'platforms' => $platforms, 'total' => $total]);
    }

    /** POST /api/admin/platforms */
    public function store(AdminPlatformRequest $request)
    {
        $data = $request->validated();

        $platform = Platform::create([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'url'           => $data['url'] ?? null,
            'category'      => $data['category'] ?? 'global',
            'level'         => $data['level'] ?? 'beginner',
            'language'      => $data['language'] ?? 'english',
            'user_count'    => (int) ($data['user_count'] ?? 0),
            'problem_count' => (int) ($data['problem_count'] ?? 0),
            'features'      => $data['features'] ?? [],
            'logo_url'      => $data['logo_url'] ?? null,
            'is_active'     => (bool) ($data['is_active'] ?? true),
        ]);

        AuditLogger::log($request, 'create_platform', 'Platform', $platform->id, ['name' => $platform->name]);

        return response()->json(['success' => true, 'message' => 'تم إضافة المنصة بنجاح', 'platform' => $platform], 201);
    }

    /** GET /api/admin/platforms/{id} */
    public function show($id)
    {
        $platform = Platform::find($id);
        if (! $platform) {
            return response()->json(['success' => false, 'message' => 'المنصة غير موجودة'], 404);
        }

        return response()->json(['success' => true, 'platform' => $platform]);
    }

    /** PUT /api/admin/platforms/{id} */
    public function update(AdminPlatformRequest $request, $id)
    {
        $platform = Platform::find($id);
        if (! $platform) {
            return response()->json(['success' => false, 'message' => 'المنصة غير موجودة'], 404);
        }

        $platform->fill($request->validated());
        $platform->save();

        AuditLogger::log($request, 'update_platform', 'Platform', $platform->id, ['name' => $platform->name]);

        return response()->json(['success' => true, 'message' => 'تم تحديث المنصة بنجاح', 'platform' => $platform]);
    }

    /** DELETE /api/admin/platforms/{id} */
    public function destroy($id)
    {
        $platform = Platform::find($id);
        if (! $platform) {
            return response()->json(['success' => false, 'message' => 'المنصة غير موجودة'], 404);
        }

        // Delete logo file — validate path stays within uploads/logos to prevent path traversal
        $logoPath = null;
        if ($platform->logo_url) {
            $logosBase = realpath(public_path('uploads/logos'));
            $resolved = realpath(public_path(ltrim(str_replace('\\', '/', $platform->logo_url), '/')));
            if ($logosBase && $resolved && str_starts_with($resolved, $logosBase) && is_file($resolved)) {
                $logoPath = $resolved;
            }
        }

        DB::transaction(function () use ($platform) {
            // Clean related records that have no FK cascade
            DB::table('platform_bookmarks')->where('platform_id', $platform->id)->delete();
            DB::table('platform_ratings')->where('platform_id', $platform->id)->delete();
            $platform->delete();
        });

        // Delete logo file only after DB transaction succeeded
        if ($logoPath) {
            @unlink($logoPath);
        }

        AuditLogger::log(request(), 'delete_platform', 'Platform', (int) $platform->id, ['name' => $platform->name]);

        return response()->json(['success' => true, 'message' => 'تم حذف المنصة بنجاح']);
    }

    /** POST /api/admin/platforms/upload-logo */
    public function uploadLogo(Request $request)
    {
        if (! $request->hasFile('logo')) {
            return response()->json(['success' => false, 'message' => 'لم يتم إرفاق ملف'], 400);
        }

        $file = $request->file('logo');
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (! in_array($file->getMimeType(), $allowed)) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مسموح به. يُسمح فقط بـ JPEG/PNG/GIF/WebP'], 400);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return response()->json(['success' => false, 'message' => 'حجم الملف يتجاوز 2MB'], 400);
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $safeName = trim(preg_replace('/_+/', '_', $safeName), '_') ?: 'platform_logo';
        // Derive extension from validated MIME type — never trust client-supplied extension
        $mimeToExt = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $ext = $mimeToExt[$file->getMimeType()] ?? 'jpg';
        $filename = 'logo_'.uniqid().'_'.$safeName.'.'.$ext;
        $logosDir = public_path('uploads/logos');

        if (! is_dir($logosDir)) {
            mkdir($logosDir, 0755, true);
        }

        $file->move($logosDir, $filename);

        return response()->json([
            'success' => true,
            'logo_url' => '/uploads/logos/'.$filename,
        ]);
    }

    // ---- Examples ----

    /** GET /api/admin/examples */
    public function examples()
    {
        $examples = Example::orderBy('id', 'desc')->get();

        return response()->json(['success' => true, 'examples' => $examples]);
    }

    /** POST /api/admin/examples */
    public function storeExample(Request $request)
    {
        if (empty(trim($request->input('title', '')))) {
            return response()->json(['success' => false, 'message' => 'عنوان المثال مطلوب.'], 400);
        }
        if (empty(trim($request->input('category', '')))) {
            return response()->json(['success' => false, 'message' => 'تصنيف المثال مطلوب.'], 400);
        }

        // Validate URL schemes to prevent stored XSS via javascript: protocol
        foreach (['demo_url', 'image_url'] as $urlField) {
            $urlVal = $request->input($urlField);
            if (! empty($urlVal) && ! preg_match('/^https?:\/\//i', $urlVal)) {
                return response()->json(['success' => false, 'message' => 'الروابط يجب أن تبدأ بـ http:// أو https://'], 400);
            }
        }

        $example = Example::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'difficulty' => $request->input('difficulty'),
            'image_url' => $request->input('image_url'),
            'code_snippet' => $request->input('code_snippet'),
            'code_language' => $request->input('code_language'),
            'technologies' => $request->input('technologies', []),
            'demo_url' => $request->input('demo_url'),
            'requires_special_env' => (bool) $request->input('requires_special_env', false),
            'special_env_message' => $request->input('special_env_message'),
            'is_active' => (bool) $request->input('is_active', true),
        ]);

        return response()->json(['success' => true, 'example' => $example], 201);
    }

    /** GET /api/admin/examples/{id} */
    public function showExample($id)
    {
        $example = Example::find($id);
        if (! $example) {
            return response()->json(['success' => false, 'message' => 'المثال غير موجود'], 404);
        }

        return response()->json(['success' => true, 'example' => $example]);
    }

    /** PUT /api/admin/examples/{id} */
    public function updateExample(Request $request, $id)
    {
        $example = Example::find($id);
        if (! $example) {
            return response()->json(['success' => false, 'message' => 'المثال غير موجود'], 404);
        }

        // N31: Validate title and category are not blanked (matches storeExample validation from N25)
        if ($request->has('title') && empty(trim($request->input('title', '')))) {
            return response()->json(['success' => false, 'message' => 'عنوان المثال مطلوب.'], 400);
        }
        if ($request->has('category') && empty(trim($request->input('category', '')))) {
            return response()->json(['success' => false, 'message' => 'تصنيف المثال مطلوب.'], 400);
        }

        // Validate URL schemes to prevent stored XSS via javascript: protocol
        foreach (['demo_url', 'image_url'] as $urlField) {
            $urlVal = $request->input($urlField);
            if ($request->has($urlField) && ! empty($urlVal) && ! preg_match('/^https?:\/\//i', $urlVal)) {
                return response()->json(['success' => false, 'message' => 'الروابط يجب أن تبدأ بـ http:// أو https://'], 400);
            }
        }

        $example->fill($request->only(['title', 'description', 'category', 'difficulty', 'image_url', 'code_snippet', 'code_language', 'technologies', 'demo_url', 'requires_special_env', 'special_env_message', 'is_active']));
        $example->save();

        return response()->json(['success' => true, 'example' => $example]);
    }

    /** DELETE /api/admin/examples/{id} */
    public function destroyExample($id)
    {
        $example = Example::find($id);
        if (! $example) {
            return response()->json(['success' => false, 'message' => 'المثال غير موجود'], 404);
        }
        $example->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف المثال بنجاح']);
    }
}

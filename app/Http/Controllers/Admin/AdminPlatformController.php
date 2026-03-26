<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\Example;
use Illuminate\Http\Request;

class AdminPlatformController extends Controller
{
    /** GET /api/admin/platforms */
    public function index()
    {
        $platforms = Platform::orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'platforms' => $platforms]);
    }

    /** POST /api/admin/platforms */
    public function store(Request $request)
    {
        $platform = Platform::create([
            'name'          => $request->input('name'),
            'description'   => $request->input('description'),
            'url'           => $request->input('url'),
            'category'      => $request->input('category', 'global'),
            'level'         => $request->input('level', 'beginner'),
            'language'      => $request->input('language', 'english'),
            'user_count'    => (int) $request->input('user_count', 0),
            'problem_count' => (int) $request->input('problem_count', 0),
            'features'      => $request->input('features', []),
            'logo_url'      => $request->input('logo_url'),
            'is_active'     => (bool) $request->input('is_active', true),
        ]);

        return response()->json(['success' => true, 'message' => 'تم إضافة المنصة بنجاح', 'platform' => $platform], 201);
    }

    /** GET /api/admin/platforms/{id} */
    public function show($id)
    {
        $platform = Platform::find($id);
        if (!$platform) {
            return response()->json(['success' => false, 'message' => 'المنصة غير موجودة'], 404);
        }
        return response()->json(['success' => true, 'platform' => $platform]);
    }

    /** PUT /api/admin/platforms/{id} */
    public function update(Request $request, $id)
    {
        $platform = Platform::find($id);
        if (!$platform) {
            return response()->json(['success' => false, 'message' => 'المنصة غير موجودة'], 404);
        }

        $platform->fill($request->only(['name', 'description', 'url', 'category', 'level', 'language', 'user_count', 'problem_count', 'features', 'logo_url', 'is_active']));
        $platform->save();

        return response()->json(['success' => true, 'message' => 'تم تحديث المنصة بنجاح', 'platform' => $platform]);
    }

    /** DELETE /api/admin/platforms/{id} */
    public function destroy($id)
    {
        $platform = Platform::find($id);
        if (!$platform) {
            return response()->json(['success' => false, 'message' => 'المنصة غير موجودة'], 404);
        }
        $platform->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف المنصة بنجاح']);
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
        $example = Example::create([
            'title'                => $request->input('title'),
            'description'         => $request->input('description'),
            'category'            => $request->input('category'),
            'difficulty'          => $request->input('difficulty'),
            'image_url'           => $request->input('image_url'),
            'code_snippet'        => $request->input('code_snippet'),
            'code_language'       => $request->input('code_language'),
            'technologies'        => $request->input('technologies', []),
            'demo_url'            => $request->input('demo_url'),
            'requires_special_env'=> (bool) $request->input('requires_special_env', false),
            'special_env_message' => $request->input('special_env_message'),
            'is_active'           => (bool) $request->input('is_active', true),
        ]);

        return response()->json(['success' => true, 'example' => $example], 201);
    }

    /** GET /api/admin/examples/{id} */
    public function showExample($id)
    {
        $example = Example::find($id);
        if (!$example) {
            return response()->json(['success' => false, 'message' => 'المثال غير موجود'], 404);
        }
        return response()->json(['success' => true, 'example' => $example]);
    }

    /** PUT /api/admin/examples/{id} */
    public function updateExample(Request $request, $id)
    {
        $example = Example::find($id);
        if (!$example) {
            return response()->json(['success' => false, 'message' => 'المثال غير موجود'], 404);
        }
        $example->fill($request->only(['title', 'description', 'category', 'difficulty', 'image_url', 'code_snippet', 'code_language', 'technologies', 'demo_url', 'requires_special_env', 'special_env_message', 'is_active']));
        $example->save();
        return response()->json(['success' => true, 'example' => $example]);
    }

    /** DELETE /api/admin/examples/{id} */
    public function destroyExample($id)
    {
        $example = Example::find($id);
        if (!$example) {
            return response()->json(['success' => false, 'message' => 'المثال غير موجود'], 404);
        }
        $example->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف المثال بنجاح']);
    }
}

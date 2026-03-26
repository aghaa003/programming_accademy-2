<?php

namespace App\Http\Controllers;

use App\Models\Example;
use Illuminate\Http\Request;

class ExampleController extends Controller
{
    /** GET /api/examples */
    public function index(Request $request)
    {
        $category   = $request->query('category', 'all');
        $difficulty = $request->query('difficulty', 'all');
        $search     = $request->query('search', '');
        $limit  = (int) $request->query('limit', 50);
        $offset = (int) $request->query('offset', 0);

        $query = Example::where('is_active', 1)
            ->select('id', 'title', 'description', 'category', 'difficulty', 'image_url',
                     'code_snippet', 'code_language', 'technologies', 'demo_url',
                     'requires_special_env', 'special_env_message', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset);

        if ($category !== 'all')   $query->where('category', $category);
        if ($difficulty !== 'all') $query->where('difficulty', $difficulty);
        if (!empty($search)) {
            $query->where(fn($q) => $q->where('title', 'like', "%$search%")->orWhere('description', 'like', "%$search%"));
        }

        $examples = $query->get()->map(function ($e) {
            $arr = $e->toArray();
            // technologies is already cast to array by the model
            if (is_string($arr['technologies'] ?? null)) {
                $arr['technologies'] = json_decode($arr['technologies'], true) ?? [];
            } elseif (!is_array($arr['technologies'] ?? null)) {
                $arr['technologies'] = [];
            }
            return $arr;
        });

        return response()->json(['success' => true, 'examples' => $examples, 'total' => $examples->count()]);
    }

    /** GET /api/examples/{id} */
    public function show($id)
    {
        $example = Example::find($id);
        if (!$example) {
            return response()->json(['success' => false, 'message' => 'Example not found'], 404);
        }
        $arr = $example->toArray();
        // technologies is already cast to array by the model
        if (is_string($arr['technologies'] ?? null)) {
            $arr['technologies'] = json_decode($arr['technologies'], true) ?? [];
        } elseif (!is_array($arr['technologies'] ?? null)) {
            $arr['technologies'] = [];
        }
        return response()->json(['success' => true, 'example' => $arr]);
    }
}

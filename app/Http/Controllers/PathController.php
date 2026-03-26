<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PathController extends Controller
{
    private const PATHS = [
        'basics' => [
            'title'    => 'مسار أساسيات البرمجة',
            'subtitle' => 'ابدأ رحلتك في عالم البرمجة من خلال تعلم الأساسيات المتينة التي تحتاجها لبناء مهاراتك البرمجية',
            'gradient' => 'linear-gradient(135deg, #4e54c8, #8f94fb)',
            'category' => 'basics',
            'icon'     => 'fa-code',
        ],
        'frontend' => [
            'title'    => 'مسار تطوير واجهات المستخدم (Frontend)',
            'subtitle' => 'احترف تطوير واجهات المستخدم التفاعلية والجذابة باستخدام أحدث التقنيات والأدوات',
            'gradient' => 'linear-gradient(135deg, #667eea, #764ba2)',
            'category' => 'frontend',
            'icon'     => 'fa-laptop-code',
        ],
        'backend' => [
            'title'    => 'مسار تطوير الخلفية (Backend)',
            'subtitle' => 'احترف بناء الخوادم وقواعد البيانات والأنظمة الخلفية التي تدعم تطبيقات الويب الحديثة',
            'gradient' => 'linear-gradient(135deg, #f093fb, #f5576c)',
            'category' => 'backend',
            'icon'     => 'fa-server',
        ],
    ];

    /** GET /api/paths/{path} */
    public function show(string $path)
    {
        if (!array_key_exists($path, self::PATHS)) {
            return response()->json(['success' => false, 'message' => 'Path not found'], 404);
        }

        return response()->json([
            'success' => true,
            'path'    => $path,
            'config'  => self::PATHS[$path],
        ]);
    }

    /** GET /api/paths */
    public function index()
    {
        return response()->json(['success' => true, 'paths' => self::PATHS]);
    }
}

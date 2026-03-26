<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    /** GET /api/admin/users */
    public function index()
    {
        $users = User::select('id', 'firstName', 'lastName', 'username', 'email', 'phone', 'country', 'experience', 'joinDate', 'is_admin')
            ->orderBy('joinDate', 'desc')
            ->get()
            ->map(function ($u) {
                $u->roles = $u->roles()->pluck('name');
                return $u;
            });

        return response()->json(['success' => true, 'users' => $users]);
    }

    /** GET /api/admin/users/{id} */
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }
        return response()->json(['success' => true, 'user' => $user]);
    }

    /** POST /api/admin/users/{id}/toggle-admin */
    public function toggleAdmin($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        // Sync admin role
        if ($user->is_admin) {
            DB::table('user_roles')->insertOrIgnore(['user_id' => $id, 'role_id' => 2]);
        } else {
            DB::table('user_roles')->where('user_id', $id)->where('role_id', 2)->delete();
        }

        return response()->json(['success' => true, 'is_admin' => $user->is_admin]);
    }

    /** DELETE /api/admin/users/{id} */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }
        $user->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف المستخدم بنجاح']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        // 1. الإحصائيات العلوية (كما في صورة إدارة المستخدمين)
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'active_percentage' => User::count() > 0 ? round((User::where('status', 'active')->count() / User::count()) * 100) : 0,
            'new_users_month' => User::whereMonth('created_at', now()->month)->count(),
        ];

        // 2. بناء الاستعلام مع الفلاتر
        $query = User::query();

        // البحث (بالاسم أو الإيميل أو الدور الوظيفي)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
                  
            });
        }

        // الفلترة حسب الدور (طبيب، فني، مريض)
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // جلب البيانات مع الترتيب والتقسيم
        $users = $query->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'statistics' => $stats,
            'data' => UserResource::collection($users),
            'pagination' => [
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

   

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,blocked'
        ]);

        $user = User::findOrFail($id);
        
        // لا نسمح للأدمن بحظر نفسه بالخطأ (حركة احترافية)
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'لا يمكنك تغيير حالة حسابك الشخصي'], 403);
        }

        $user->update(['status' => $request->status]);

        // إذا تم الحظر، نقوم بإبطال جميع الـ Tokens الخاصة به فوراً ليطرده الميدل وير
        if ($request->status === 'blocked') {
            $user->tokens()->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث حالة المستخدم بنجاح',
            'data' => new UserResource($user)
        ]);
    }

    // 3. إعادة تعيين كلمة المرور - مخصص لطلب الـ Reset Password 🚀
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        
        // توليد كلمة مرور عشوائية بسيطة
        $tempPassword = Str::random(10); 
        
        $user->update([
            'password' => Hash::make($tempPassword)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إعادة تعيين كلمة المرور بنجاح',
            'temp_password' => $tempPassword // نرسلها في الـ API ليراها الأدمن ويعطيها للمستخدم
        ]);
    }
}

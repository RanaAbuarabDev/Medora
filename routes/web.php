<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/google/callback', function () {


    $googleUser = Socialite::driver('google')->user();
    $user = User::where('email', $googleUser->email)->first();
    if (!$user) {
        return response()->json(['message' => 'هذا الحساب غير مسجل في نظام ميدورا'], 403);
    }
    $token = $user->createToken('medora-token')->plainTextToken;

    return redirect("https://www.ushare.vip/login?token=$token");
});
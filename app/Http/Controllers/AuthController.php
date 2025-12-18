<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\OtpVerificationService;



class AuthController extends Controller
{


    protected $AuthService;

    public function __construct(AuthService $authService)
    {
        $this->AuthService = $authService;
    }


    public function login(LoginRequest $request)
    {
        try {
            $data = $this->AuthService->login($request->validated());

            return ApiResponseService::success(
                [
                    'user' => $data['user'],
                    'authorisation' => [
                        'token' => $data['token'],
                        'type'  => 'bearer'
                    ]
                ],
                "Login Successful",
                200
            );

        } catch (\Exception $e) {
            return ApiResponseService::error(
                $e->getMessage(),
                "Unauthorized",
                401
            );
        }
    }




    public function registerPatient(RegisterRequest $request){

        $this->AuthService->registerPatient(
            $request->validated()
        );

        return ApiResponseService::success(
            [],
            'تم إرسال رمز التحقق إلى بريدك الإلكتروني',
            201
        );
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }


    public function refresh(Request $request)
    {
        $request->user()->tokens()->delete();

        $token = $request->user()->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }



    

    public function verifyRegistrationOtp(Request $request,OtpVerificationService $otpVerificationService) {


        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|string'
        ]);

        $result = $otpVerificationService->verify(
            $request->email,
            $request->otp
        );

        if (! $result['success']) {
            return ApiResponseService::error(
                $result['message'],
                422
            );
        }

        return ApiResponseService::success(
            [
                'token' => $result['token'],
                'type'  => $result['type']
            ],
            'تم التحقق من البريد الإلكتروني بنجاح'
        );
    }

  



}

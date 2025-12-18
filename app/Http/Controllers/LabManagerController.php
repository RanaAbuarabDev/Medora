<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use Illuminate\Http\Request;


class LabManagerController extends Controller
{


    protected $AuthService;
    public function __construct(AuthService $authService)
    {
        $this->AuthService = $authService;
    }


    
    public function createAssistant(RegisterRequest $request)
    {
        $this->AuthService->addLabAssistant($request->validated());

        return ApiResponseService::success(
            [],
            'Assistant created successfully',
            201
        );
    }



    public function createReceptionist(RegisterRequest $request){

        $this->AuthService->addReceptionist($request->validated());

        return ApiResponseService::success(
            [],
            'Receptionist created successfully',
            201
        );
    }
}

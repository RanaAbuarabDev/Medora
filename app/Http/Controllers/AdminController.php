<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    protected $AuthService;

    public function __construct(AuthService $authService)
    {
        $this->AuthService = $authService;
    }



    public function createLabManager(RegisterRequest $request){
        
        $this->AuthService->addLabManager(
            $request->validated()
        );

        return ApiResponseService::success(
            [],
            'The opertation succeeded',
            201
        );
    }
}

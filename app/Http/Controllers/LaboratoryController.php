<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLab;
use App\Services\ApiResponseService;
use App\Services\labService;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{

    protected $labService;
    public function __construct(labService $labService)
    {
        $this->labService = $labService;
    }


    public function store(CreateLab $request) {
        
        $data = $this->labService->createNewLabWithManager($request->validated());
        
        return ApiResponseService::success([$data], 'Lab and Manager created successfully');
    }
}

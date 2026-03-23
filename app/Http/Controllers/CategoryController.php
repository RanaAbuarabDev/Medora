<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\TestCategory;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;

class CategoryController extends Controller
{
    
    public function index()
    {
        $categories = TestCategory::all();
        return ApiResponseService::success($categories, 'Categories retrieved successfully');
    }


   
    public function store(StoreCategoryRequest $request)
    {
    
        $category = TestCategory::create($request->validated());
        return ApiResponseService::success($category, 'Category created successfully', 201);
    }

    
    
    public function show($id)
    {
       
        $category = TestCategory::findOrFail($id);
        
        return ApiResponseService::success($category, 'Category retrieved successfully');
    }

    
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = TestCategory::findOrFail($id);
        $category->update($request->validated());
        return ApiResponseService::success($category, 'Category updated successfully');
    }

    
    public function destroy($id)
    {
        $category = TestCategory::findOrFail($id);
        $category->delete();
        return ApiResponseService::success([], 'Category deleted successfully');
    }
}
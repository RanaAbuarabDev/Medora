<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponseService
{

    /**
     * Return a successful JSON Response
     * 
     * @param mixed $data the data to return in the response 
     * @param string $message success message 
     * @param int $status the HTTP  status code 
     * 
     * @return \Illuminate\HTTP\JsonResponse the json response
     */
    public static function success($data = null  ,$message = "oprating successful", $status = 200){
        return response()->json([
            'data'=>$data,
            'message'=>trans($message),
            'status'=>'success'
        ],$status);
    }


    public static function error($data = null  ,$message = "oprating failer", $status = 400){
        return response()->json([
            'data'=>$data,
            'message'=>trans($message),
            'status'=>'error'
        ],$status);
    }

    public static function paginated(LengthAwarePaginator $paginator, $message='Operation successeful',$status = 200)
    {
        return response()->json([
            'status'=>'success',
            'message'=> trans($message),
            'data'=>$paginator->items(),
            'pagination'=>[
                'total'=>$paginator->total(),
                'count'=>$paginator->count(),
                'per_page'=>$paginator->perPage(),
                'current_page'=>$paginator->currentPage(),
                'total_pages'=>$paginator->lastPage()
            ]
        ],$status);
    }
}
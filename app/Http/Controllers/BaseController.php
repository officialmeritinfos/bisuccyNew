<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function sendError($error, $errorMessages,$code=404)
    {
        $response = [
            'error' => true,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response,400);
    }
    public function sendResponse($result, $message='process successful',$code=200)
    {
        $response = [
            'error'   => 'ok',
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, 200);
    }
}

<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function responseGetSimplePaginate($raw)
    {

        $data = collect($raw)['data'];
        $meta = collect($raw)->except('data');
        return [
            'data' => $data,
            'meta' => $meta
        ];
    }
    public static function responseStore($data, $message = '',  $code = 200, $side = null)
    {

        return new JsonResponse([
            'data' => $data,
            'side' => $side,
            'message' => $message
        ], $code);
    }
}

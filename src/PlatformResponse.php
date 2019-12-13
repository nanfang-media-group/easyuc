<?php

namespace SouthCN\EasyUC;

use Illuminate\Http\JsonResponse;

class PlatformResponse extends JsonResponse
{
    public function __construct($code, $message = '', $status = 200)
    {
        parent::__construct([
            'errcode'    => $code,
            'errmessage' => $message,
            'data'       => null,
        ], $status);
    }
}

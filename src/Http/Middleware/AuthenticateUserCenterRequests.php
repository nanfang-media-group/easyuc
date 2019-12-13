<?php

namespace SouthCN\EasyUC\Http\Middleware;

use Closure;
use SouthCN\EasyUC\PlatformResponse;

class AuthenticateUserCenterRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure                  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('easyuc.debug')) {
            return $next($request);
        }

        if ($request->time < now()->subMinutes(5)->timestamp) {
            return $this->errorResponse(30004, '时间不合法');
        }

        if ($request->token != $this->calculateToken($request->time)) {
            return $this->errorResponse(30005, 'Token不合法');
        }

        return $next($request);
    }

    protected function calculateToken($time)
    {
        return md5(
            config('easyuc.app') . $time . config('easyuc.ticket')
        );
    }

    protected function errorResponse($code, $message)
    {
        return new PlatformResponse($code, $message, 403);
    }
}

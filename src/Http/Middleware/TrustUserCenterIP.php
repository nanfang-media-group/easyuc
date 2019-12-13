<?php

namespace SouthCN\EasyUC\Http\Middleware;

use Closure;

class TrustUserCenterIP
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
        if ($ucHost = config('easyuc.oauth.ip')) {
            ('all' == $ucHost || $ucHost == $request->ip())
            or abort(403, "信号并非来自可信的用户中心IP：{$request->ip()}");
        }

        return $next($request);
    }
}

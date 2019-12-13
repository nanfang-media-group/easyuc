<?php

namespace SouthCN\EasyUC\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use SouthCN\EasyUC\Exceptions\ConfigUndefinedException;
use SouthCN\EasyUC\Service;

class PlatformLogout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure                  $next
     * @return mixed
     * @throws ConfigUndefinedException
     */
    public function handle($request, Closure $next)
    {
        // 未登入时，无需检查统一登出信号
        if (Auth::guest()) {
            return $next($request);
        }

        // 登出页面，无需检查统一登出信号
        if (config('easyuc.route.logout') == $request->path()) {
            return $next($request);
        }

        if (Service::logoutSignal()->check()) {
            $request->session()->invalidate();

            return redirect(config('easyuc.route.login'));
        }

        return $next($request);
    }
}

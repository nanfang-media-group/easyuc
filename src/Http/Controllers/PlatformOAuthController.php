<?php

namespace SouthCN\EasyUC\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use SouthCN\EasyUC\Exceptions\ApiFailedException;
use SouthCN\EasyUC\Exceptions\ConfigUndefinedException;
use SouthCN\EasyUC\Exceptions\UnauthorizedException;
use SouthCN\EasyUC\PlatformResponse;
use SouthCN\EasyUC\Repositories\UserCenterAPI;
use SouthCN\EasyUC\Repository;
use SouthCN\EasyUC\Service;

class PlatformOAuthController extends Controller
{
    /**
     * 处理平台 OAuth 回调，并实现统一登入
     *
     * @throws ApiFailedException
     * @throws UnauthorizedException
     * @throws ConfigUndefinedException
     */
    public function login(Request $request)
    {
        $response = (new UserCenterAPI)->getUserDetail($request->access_token);
        $repository = new Repository($response);

        Auth::login($this->syncUser($repository));

        // 直接使用响应中的 token，$repository->token->logout 有缓存不更新的问题
        Service::token()->logout = $response->logout_token;
        Cache::forever("token:{$response->logout_token}:session", Session::getId());
        Log::debug('UC_TOKEN_IN_RESPONSE: ' . $response->logout_token);

        return redirect(config('easyuc.oauth.redirect_url'));
    }

    /**
     * 处理用户中心主动登出
     * 此方法由用户中心服务端调用，因此是处于【无状态环境】
     */
    public function logout(Request $request)
    {
        // 发出用户中心登出信号
        Service::logoutSignal($request->logout_token)->set();

        return new PlatformResponse(0, 'ok');
    }

    /**
     * @throws ApiFailedException
     * @throws UnauthorizedException
     * @throws ConfigUndefinedException
     */
    protected function syncUser(Repository $repository): Authenticatable
    {
        $userHandler = app('easyuc.user.handler');

        // 需要有 APP 授权才可进入，即使是超管
        if ($repository->authorized()) {
            return $userHandler($repository);
        }

        throw new UnauthorizedException('管理中心未授权此用户');
    }
}

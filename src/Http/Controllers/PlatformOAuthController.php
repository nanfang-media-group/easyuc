<?php

namespace SouthCN\EasyUC\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
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
     * @var Repository
     */
    protected $repository;

    /**
     * 处理平台 OAuth 回调，并实现统一登入
     *
     * @throws ApiFailedException
     * @throws UnauthorizedException
     * @throws ConfigUndefinedException
     */
    public function login()
    {
        Auth::login($this->syncUser());

        Service::token()->logout = $this->repository->token->logout;

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
    protected function syncUser(): Authenticatable
    {
        $this->repository = new Repository(
            (new UserCenterAPI)->getUserDetail(request('access_token'))
        );

        $userHandler = app('easyuc.user.handler');

        // 需要有 APP 授权才可进入，即使是超管
        if ($this->repository->authorized()) {
            return $userHandler($this->repository);
        }

        throw new UnauthorizedException('管理中心未授权此用户');
    }
}

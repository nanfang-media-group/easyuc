<?php

namespace SouthCN\EasyUC\Repositories;

use AbelHalo\ApiProxy\ApiProxy;
use Illuminate\Support\Facades\Config;
use SouthCN\EasyUC\Exceptions\ApiFailedException;
use SouthCN\EasyUC\Service;
use SouthCN\PrivateApi\PrivateApi;

class UserCenterAPI
{
    protected $proxy;

    public function __construct()
    {
        $this->proxy = (new ApiProxy)->returnAsObject();

        Config::set('private-api._', ['return_type' => 'object']);
        Config::set('private-api.easyuc', [
            'app' => env('UC_APP'),
            'ticket' => env('UC_TICKET'),

            'sync-service-area-list' => ['url' => config('easyuc.oauth.base_url') . '/api/private/sync/servicearea/list'],
            'sync-org-list' => ['url' => config('easyuc.oauth.base_url') . '/api/private/sync/org/list'],
            'sync-site-list' => ['url' => config('easyuc.oauth.base_url') . '/api/private/sync/site/list'],
            'sync-user-list' => ['url' => config('easyuc.oauth.base_url') . '/api/private/sync/user/list'],
        ]);
    }

    /**
     * 用户中心「获取用户详细信息」接口
     *
     * @return object
     * @throws ApiFailedException
     */
    public function getUserDetail(string $accessToken)
    {
        $url = config('easyuc.oauth.base_url') . '/api/oauth/user/detail';

        /** @var object $response */
        $response = $this->proxy->post($url, [
            'access_token' => $accessToken,
            'site_app_id' => config('easyuc.site_app_id'),
            'service_area_ids' => null,
        ]);

        if (empty($response->data)) {
            throw new ApiFailedException("调用 $url 接口失败：{$response->errmessage}");
        }

        return $response->data;
    }

    /**
     * 用户中心「获取服务区列表」接口
     *
     * @throws ApiFailedException
     */
    public function getServiceAreaList(): array
    {
        $response = PrivateApi::app('easyuc')->api('sync-service-area-list');

        if (empty($response->data)) {
            throw new ApiFailedException("调用 sync-service-area-list 接口失败：{$response->errmessage}");
        }

        return $response->data->list;
    }

    /**
     * 用户中心「获取单位列表」接口
     *
     * @throws ApiFailedException
     */
    public function getOrgList(?array $serviceAreas = null): array
    {
        $response = PrivateApi::app('easyuc')->api('sync-org-list', [
            'service_area_ids' => $serviceAreas,
        ]);

        if (empty($response->data)) {
            throw new ApiFailedException("调用 sync-org-list 接口失败：{$response->errmessage}");
        }

        return $response->data->list;
    }

    /**
     * 用户中心「获取站点列表」接口
     *
     * @throws ApiFailedException
     */
    public function getSiteList(?int $siteAppId = null, ?array $serviceAreas = null): array
    {
        if (is_null($siteAppId)) {
            $siteAppId = config('easyuc.site_app_id');
        }

        if (!is_null($serviceAreas)) {
            $serviceAreas = implode(',', $serviceAreas);
        }

        $response = PrivateApi::app('easyuc')->api('sync-site-list', [
            'site_app_id' => $siteAppId,
            'service_area_ids' => $serviceAreas,
        ]);

        if (empty($response->data)) {
            throw new ApiFailedException("调用 sync-site-list 接口失败：{$response->errmessage}");
        }

        return $response->data->list;
    }

    /**
     * 用户中心「获取用户信息列表」接口
     *
     * @throws ApiFailedException
     */
    public function getUserList(): array
    {
        $response = PrivateApi::app('easyuc')->api('sync-user-list', [
            'site_app_id' => config('easyuc.site_app_id'),
        ]);

        if (empty($response->data)) {
            throw new ApiFailedException("调用 sync-user-list 接口失败：{$response->errmessage}");
        }

        return $response->data->list;
    }

    /**
     * 用户中心「统一登出」接口
     *
     * @throws ApiFailedException
     */
    public function logout(): void
    {
        $url   = config('easyuc.oauth.logout_url');
        $token = Service::token()->logout;

        // 被动登出情景下，无需再向用户中心通知登出
        if (Service::logoutSignal()->check()) {
            Service::logoutSignal()->clear();
            return;
        }

        /** @var object $response */
        $response = $this->proxy->post($url, [
            'logout_token' => $token,
        ]);

        if (0 !== $response->errcode) {
            throw new ApiFailedException("调用 $url 接口失败：{$response->errmessage}（Token={$token}）");
        }

        unset(Service::token()->logout);
    }
}

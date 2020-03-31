<?php

namespace SouthCN\EasyUC\Repositories;

use AbelHalo\ApiProxy\ApiProxy;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use SouthCN\EasyUC\Exceptions\ApiFailedException;
use SouthCN\EasyUC\Service;
use SouthCN\PrivateApi\PrivateApi;
use stdClass;

class UserCenterAPI
{
    protected $proxy;

    public function __construct()
    {
        $this->proxy = (new ApiProxy)->returnAsObject();
        $this->proxy->logger->enable();

        Config::set('private-api._', ['return_type' => 'object']);
        Config::set('private-api.easyuc', [
            'app' => config('easyuc.app'),
            'ticket' => config('easyuc.ticket'),

            'sync-service-area-list' => ['url' => config('easyuc.oauth.base_url') . '/api/private/sync/servicearea/list'],
            'sync-org-list' => ['url' => config('easyuc.oauth.base_url') . '/api/private/sync/org/list'],
            'sync-site-list' => ['url' => config('easyuc.oauth.base_url') . '/api/private/sync/site/list'],
            'sync-user-list' => ['url' => config('easyuc.oauth.base_url') . '/api/private/sync/chunk/user/list'],
        ]);
        Config::set('logging.channels.easyuclog', [
            'driver' => 'daily',
            'path' => storage_path('logs/easyuc-response.log'),
            'level' => 'debug',
            'days' => 7,
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

        $this->logResponse($response);

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

        $this->logResponse($response);

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

        $this->logResponse($response);

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

        $this->logResponse($response);

        if (empty($response->data)) {
            throw new ApiFailedException("调用 sync-site-list 接口失败：{$response->errmessage}");
        }

        return $response->data->list;
    }

    /**
     * 用户中心「获取用户信息列表」接口
     *
     * @param  string  $type  all 表示全量同步，inc 表示增量同步
     * @param  string  $period  同步周期
     * @param  int  $version  起始版本（全量同步或第一次同步时传 0 ）
     * @return array
     * @throws ApiFailedException
     */
    public function getUserList(string $type, string $period, int $version = 0): stdClass
    {
        $response = PrivateApi::app('easyuc')->api('sync-user-list', [
            'sync_period_id' => $period,
            'site_app_id' => config('easyuc.site_app_id'),
            'sync_option' => $type,
            'from_version' => $version,
        ]);

        $this->logResponse($response);

        if (empty($response->data)) {
            throw new ApiFailedException("调用 sync-user-list 接口失败：{$response->errmessage}");
        }

        return $response->data;
    }

    /**
     * 用户中心「统一登出」接口
     *
     * @throws ApiFailedException
     */
    public function logout(string $session = ''): void
    {
        $url = config('easyuc.oauth.logout_url');
        $token = Service::token($session)->logout;

        if (!$token) {
            exit('logout token is null');
        }

        // 被动登出情景下，无需再向用户中心通知登出
        if (Service::logoutSignal()->check()) {
            Service::logoutSignal()->clear();
            return;
        }

        /** @var object $response */
        $response = $this->proxy->post($url, [
            'logout_token' => $token,
        ]);

        $this->logResponse($response);

        if (0 !== $response->errcode) {
            throw new ApiFailedException("调用 $url 接口失败：{$response->errmessage}（Token={$token}）");
        }

        unset(Service::token()->logout);
    }

    protected function logResponse(stdClass $response): void
    {
        Log::channel('easyuclog')->debug('RESPONSE', json_decode(json_encode($response), true));
    }
}

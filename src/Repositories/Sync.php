<?php

namespace SouthCN\EasyUC\Repositories;

use Illuminate\Foundation\Auth\User;
use SouthCN\EasyUC\Contracts\ShouldSyncOrgs;
use SouthCN\EasyUC\Contracts\ShouldSyncServiceAreas;
use SouthCN\EasyUC\Contracts\ShouldSyncSites;
use SouthCN\EasyUC\Contracts\ShouldSyncUser;
use SouthCN\EasyUC\Contracts\ShouldSyncUserSites;
use SouthCN\EasyUC\Repositories\Data\ServiceAreaList;
use SouthCN\EasyUC\Repositories\Data\SiteList;
use SouthCN\EasyUC\Repositories\Data\User as UserData;

class Sync
{
    protected $ucAPI;
    protected $userHandler;

    public function __construct()
    {
        $this->ucAPI       = new UserCenterAPI;
        $this->userHandler = app('easyuc.user.handler');
    }

    /**
     * 主动或被动的「同步用户」操作
     */
    public function users(): void
    {
        if (!($this->userHandler instanceof ShouldSyncUser)) {
            return;
        }

        ticker_timer_begin('sync.user');

        ticker_timer_begin('sync.user.get_user_list');
        $userList = $this->ucAPI->getUserList();
        ticker_timer_end('sync.user.get_user_list');

        ticker_timer_begin('sync.user.updating');
        foreach ($userList as $data) {
            $userData   = new UserData($data->user);
            $existing[] = $data->user->id;

            // 同步用户信息
            ticker_timer_begin('sync.user.updating.user');
            $user = $this->userHandler->syncUser($userData);
            ticker_timer_end('sync.user.updating.user');

            // 同时，必须同步用户的站点权限
            if ($this->userHandler instanceof ShouldSyncUserSites) {
                ticker_timer_begin('sync.user.updating.user_sites');
                $this->helpSyncUserSites($user, $data);
                ticker_timer_end('sync.user.updating.user_sites');
            }
        }
        ticker_timer_end('sync.user.updating');

        // 反向删除「存在」以外的用户
        ticker_timer_begin('sync.user.removing');
        $this->userHandler->removeUsers($existing ?? []);
        ticker_timer_end('sync.user.removing');

        ticker_timer_end('sync.user');
    }

    /**
     * 主动或被动的「同步站点」操作
     */
    public function sites(): void
    {
        if ($this->userHandler instanceof ShouldSyncServiceAreas) {
            $this->userHandler->syncServiceAreas(
                new ServiceAreaList($this->ucAPI->getServiceAreaList())
            );
        }

        if ($this->userHandler instanceof ShouldSyncOrgs) {
            $this->userHandler->syncOrgs(
                $this->ucAPI->getOrgList()
            );
        }

        if ($this->userHandler instanceof ShouldSyncSites) {
            $this->userHandler->syncSites(
                new SiteList($this->ucAPI->getSiteList())
            );
        }
    }

    protected function helpSyncUserSites(User $user, \stdClass $data): void
    {
        $userData = new UserData($data->user);

        if ($userData->super()) {
            $this->userHandler->syncUserAppSites($user);
        }

        if ($userData->serviceAreaAdmin()) {
            $this->userHandler->syncUserServiceAreas($user, new ServiceAreaList($data->service_area_list));
        }

        if ($userData->normalUser()) {
            $this->userHandler->syncUserSites($user, new SiteList($data->site_list));
        }
    }
}

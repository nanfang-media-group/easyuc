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
use SouthCN\EasyUC\Repository;
use stdClass;

class Sync
{
    protected $ucAPI;
    protected $userHandler;

    public function __construct()
    {
        $this->ucAPI = new UserCenterAPI;
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

        $userList = $this->ucAPI->getUserList();

        foreach ($userList as $data) {
            $userData   = new UserData($data->user);
            $existing[] = $data->user->id;

            // 同步用户信息
            $user = $this->userHandler->syncUser($userData);

            // 同时，必须同步用户的站点权限
            if ($this->userHandler instanceof ShouldSyncUserSites) {
                $this->helpSyncUserSites($user, $data);
            }
        }

        // 反向删除「存在」以外的用户
        $this->userHandler->removeUsers($existing ?? []);
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

    protected function helpSyncUserSites(User $user, stdClass $data): void
    {
        $repository = new Repository($data);

        if ($repository->user->super()) {
            $this->userHandler->syncUserAppSites($user);
        }

        if ($repository->user->serviceAreaAdmin()) {
            $this->userHandler->syncUserServiceAreas($user, $repository);
        }

        if ($repository->user->normalUser()) {
            $this->userHandler->syncUserSites($user, $repository);
        }
    }
}

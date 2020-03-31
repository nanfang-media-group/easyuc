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
    public function users($fullSync = false): void
    {
        if (!($this->userHandler instanceof ShouldSyncUser)) {
            return;
        }

        $bridge = new UserCenterBridge;
        $processUsers = function (array $userList) {
            foreach ($userList as $data) {
                // 同步用户信息
                $userData = new UserData($data->user);
                $user = $this->userHandler->syncUser($userData, $data->operation);

                // 同时，必须同步用户的站点权限
                if ($user && $this->userHandler instanceof ShouldSyncUserSites) {
                    $this->helpSyncUserSites($user, $data);
                }
            }
        };

        if ($fullSync) {
            $bridge->allUsers($processUsers);
        } else {
            $bridge->chunkUsers($processUsers);
        }
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

<?php

namespace SouthCN\EasyUC\Repositories;

use Illuminate\Support\Facades\Cache;

class UserCenterBridge
{
    protected $api;
    protected $period;

    public function __construct()
    {
        $this->api = new UserCenterAPI;
        $this->period = md5(uniqid(config('easyuc.site_app_id')));
    }

    public function allUsers(callable $callback): void
    {
        $version = 0;
        $finished = false;

        while (!$finished) {
            $response = $this->api->getUserList('all', $this->period, $version);

            if (count($response->list)) {
                $callback($response->list);
            }

            $version = $response->version;
            $finished = $response->is_finished;
        }

        Cache::forever('uc:sync:user:version', $version);
    }

    public function chunkUsers(callable $callback): void
    {
        $version = Cache::get('uc:sync:user:version', 0);
        $finished = false;

        while (!$finished) {
            $response = $this->api->getUserList('inc', $this->period, $version);

            if (count($response->list)) {
                $callback($response->list);
            }

            $version = $response->version;
            $finished = $response->is_finished;

            Cache::forever('uc:sync:user:version', $version);
        }
    }
}

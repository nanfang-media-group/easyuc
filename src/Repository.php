<?php

namespace SouthCN\EasyUC;

use SouthCN\EasyUC\Repositories\Data\ServiceAreaList;
use SouthCN\EasyUC\Repositories\Data\SiteList;
use SouthCN\EasyUC\Repositories\Data\Token;
use SouthCN\EasyUC\Repositories\Data\User;

class Repository
{
    public $data;
    public $user;
    public $serviceAreas;
    public $sites;
    public $token;

    public function __construct($data)
    {
        $this->data         = $data;
        $this->user         = new User($data->user);
        $this->serviceAreas = new ServiceAreaList($data->service_area_list);
        $this->sites        = new SiteList($data->site_list);
        $this->token        = new Token([
            'access' => request('access_token'),
            'logout' => $data->logout_token,
        ]);
    }

    /**
     * 确认用户是否拥有本应用的权限
     */
    public function authorized(): bool
    {
        // 管理员，或站点用户旗下的站点有开启了本应用的
        return $this->user->group < 10 || count($this->sites->data);
    }

    public function __get($name)
    {
        return $this->$name;
    }
}

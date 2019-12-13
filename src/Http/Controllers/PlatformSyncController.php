<?php

namespace SouthCN\EasyUC\Http\Controllers;

use Illuminate\Routing\Controller;
use SouthCN\EasyUC\PlatformResponse;
use SouthCN\EasyUC\Service;

class PlatformSyncController extends Controller
{
    public function syncUser()
    {
        Service::sync()->users();

        return new PlatformResponse(0, 'ok');
    }

    public function syncSites()
    {
        Service::sync()->sites();

        return new PlatformResponse(0, 'ok');
    }
}

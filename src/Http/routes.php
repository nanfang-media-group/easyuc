<?php

use Illuminate\Support\Facades\Route;
use SouthCN\EasyUC\Http\Middleware\TrustUserCenterIP;

Route::prefix(config('easyuc.route.prefix'))->group(function () {

    Route::middleware('web')->group(function () {
        // 统一登入
        Route::get('uc/obtain-token', 'SouthCN\EasyUC\Http\Controllers\PlatformOAuthController@login');

        // 统一登出
        Route::middleware(TrustUserCenterIP::class)
             ->any('uc/logout', 'SouthCN\EasyUC\Http\Controllers\PlatformOAuthController@logout');

        // 被动同步用户
        Route::middleware(TrustUserCenterIP::class)
             ->post('uc/sync-user', 'SouthCN\EasyUC\Http\Controllers\PlatformSyncController@syncUser');

        // 被动同步站点
        Route::middleware(TrustUserCenterIP::class)
             ->post('uc/sync-sites', 'SouthCN\EasyUC\Http\Controllers\PlatformSyncController@syncSites');
    });

});

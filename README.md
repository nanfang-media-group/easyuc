# Easy UC

Easy UC 是为方便<u>平台 APP</u> 与<u>平台用户中心</u>对接而打造的 Laravel 扩展包，可极大地减少重复工作量：

1. 无需理会跟<u>平台用户中心</u>交互的技术细节（HTTP、授权逻辑等）
2. 无需反复搭建繁琐的路由、控制器等结构
3. 只需专注子系统内部的业务逻辑



## 主要功能

- 平台定制的 OAuth 授权
- 主动和被动的统一登入
- 主动和被动的统一登出



## 环境要求

- PHP >= 7.1
- Laravel >= 5.6



## 认证流程

### 登入

1. <u>平台 APP</u> 判断当前未登入，跳去<u>平台用户中心</u>登入页面（示例：`http(s)://platform.domain/usercenter/login?appid=应用注册的值`）
2. <u>平台用户中心</u>登入成功后，会回调跳转到 APP 提供的回调地址（默认 `http(s)://platform-app.domain/uc/obtain-token`）
3. Easy UC 注册的 `uc/obtain-token` 路由接手登入回调过程，自动调用 Laravel 的 `Auth:login` 方法实现 APP 内登入

### 登出

1. <u>平台 APP</u> 内部做登出处理，比如 `Auth:logout` ，完成<u>平台 APP</u> 层面登出
2. 向<u>平台用户中心</u>发送登出信号，以通知<u>平台 APP</u> 进行统一登出
3. 跳转去<u>平台用户中心</u>的登出地址，完成平台层面登出



## 使用说明

### 安装

在 Laravel 项目中引入 Composer 包：

```shell
composer require southcn/easyuc
```



### 配置

如需修改默认配置，可发布配置文件：

```php
php artisan vendor:publish --provider="SouthCN\EasyUC\ServiceProvider"
```

在项目 `.env` 文件里加上如下**必要**的配置项：

```
UC_APP=
UC_TICKET=
UC_SITE_APP_ID=
UC_ROUTE_LOGOUT=
UC_OAUTH_TRUSTED_IP=
UC_OAUTH_REDIRECT=
UC_BASE_URL=
```

如不了解 ENV 配置项的作用，可先发布配置文件，然后查看配置文件的注释。



### 服务提供者

首先在 `AppServiceProvider` 的 `register` 方法添加一行，如：

```php
public function register()
{
  	// 实现了 __invoke 方法的类
		$this->app->bind('easyuc.user.handler', UserCenterUserHandler::class);
  
  	// 或者是一个闭包
  	$this->app->bind(
	      'easyuc.user.handler', 
        function (\SouthCN\EasyUC\Repository $repository) {
          // 业务逻辑……
        }
    );
}
```

`UserCenterUserHandler` 类必须实现 `__invoke` 魔术方法，可放在任意目录。



### 定时任务

因某些用户组的用户在统一登入的 OAuth 回调中，不再携带有站点列表，<u>平台 APP</u> 必须自行实现一个定时任务——每小时拉取一次站点列表。<u>平台 APP</u> 在发生首次登入之前，必须先完整拉取一次站点列表。

配置定时任务：

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('uc:sync-sites')->hourly();
        $schedule->command('uc:sync-users')->hourly();
    }
}

```



### 路由

要确认 Easy UC 的路由是否成功注册，可查看项目中已注册的路由：

```shell
php artisan route:list | grep uc
```



## 统一登入

### 业务逻辑

在`UserCenterUserHandler` 类编写<u>平台 APP</u> 内部的业务逻辑。

注：Easy UC 已内置了管理中心应用授权判断逻辑，无需重复实现。



### 自定义控制器逻辑

Easy UC 会自动注册一条 `uc/obtain-token` 路由，如要定制控制器逻辑，只需自行再注册一条 `uc/obtain-token` 路由：

```php
// routes/web.php
Route::get('uc/obtain-token', 'PlatformOAuthController@login');


// app/Http/Controllers/PlatformOAuthController.php
namespace App\Http\Controllers;

class PlatformOAuthController extends \SouthCN\EasyUC\Controllers\PlatformOAuthController
{
    public function login()
    {
        parent::login();

        // 此处演示自定义跳转逻辑
        return redirect("/#/?token=" . session('token'));
    }
}
```



## 统一登出

### 中间件

<u>平台 APP</u> 需要在适当位置添加 `\SouthCN\EasyUC\Http\Middleware\PlatformLogout::class` 中间件，以监听平台统一登出信号。



### 控制器

以 Laravel 默认配置为例，默认的登出路径是 `POST /logout`，对应 `'Auth\LoginController@logout'`。为接入统一登出，需要修改 `'Auth\LoginController@logout'` 方法：

```php
// app/Http/Controllers/Auth/LoginController.php

public function logout(Request $request, \SouthCN\EasyUC\UserCenterApi $ucApi)
{
    // 通知平台用户中心进行统一登出操作
	  // 被动登出情景下，无需再向平台用户中心通知登出
	  // 此方法已自动处理此情景
    $ucApi->logout();

    // 原有登出逻辑……
}
```



## 数据同步

### 同步用户

要实现用户同步功能， `UserCenterUserHandler` 类必须实现 `SouthCN\EasyUC\Contracts\ShouldSyncUser` 接口

要实现用户站点权限同步功能， `UserCenterUserHandler` 类必须实现 `SouthCN\EasyUC\Contracts\ShouldSyncUserSites` 接口

**主动同步（命令行触发）**

Easy UC 会自动注册一条 Artisan 命令，用于主动同步：

```
php artisan uc:sync-users
```

**被动同步（平台触发）**

1. Easy UC 会自动注册 `uc/sync-user` 路由用于平台触发用户同步
2. 同时需要在<u>平台用户中心</u>的 `site_app` 表配置 `sync_user_url` 
3. **为避免不必要的复杂度，Easy UC 仅实现同步操作，如有其它性能需求，需自行重写对应逻辑**




### 同步站点

要实现站点同步功能， `UserCenterUserHandler` 类必须实现 `SouthCN\EasyUC\Contracts\ShouldSyncSites` 接口

**主动同步（命令行触发）**

Easy UC 会自动注册一条 Artisan 命令，用于主动同步：

```
php artisan uc:sync-sites
```

**被动同步（平台触发）**

1. Easy UC 会自动注册 `uc/sync-sites` 路由用于平台触发站点同步
2. 同时需要在<u>平台用户中心</u>的 `site_app` 表配置 `sync_org_struct_url` 字段 
3. **为避免不必要的复杂度，Easy UC 仅实现同步操作，如有其它性能需求，需自行重写对应逻辑**

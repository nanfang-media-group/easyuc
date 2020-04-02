# Easy UC

## 安装

### 服务器要求

- PHP ≥ 7.1
- Laravel ≥ 5.6



### 安装 Easy UC

因为 Easy UC 没发布到 Packagist，所以需要在项目 composer.json 文件中添加 repositories 配置段：

```json
{
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/nanfang-media-group/private-api.git"
    },
    {
      "type": "git",
      "url": "https://github.com/nanfang-media-group/easyuc.git"
    }
  ]
}
```

（ Easy UC 依赖于 southcn/easyuc ）

通过 Composer 安装：

```bash
composer require southcn/easyuc:dev-master
```



### 配置

发布配置文件到 config/easyuc.php：

```bash
php artisan vendor:publish --provider="SouthCN\EasyUC\EasyUCServiceProvider"
```

在 Laravel 框架启动阶段，为避免人为失误，Easy UC 会对配置完整性进行自检，**如有未配置的必要项，将直接抛出异常。**部分配置片段默认已被注释，如需使用被注释的功能，去除注释即可。



## 使用 API

Easy UC 对用户中心的主要 API 做了封装，免除了接口配置、HTTP 交互、接口日志、反复调试等烦恼。

```php
use SouthCN\EasyUC\Repositories\UserCenterAPI;

$api = new UserCenterAPI;

$api->getUserDetail(); // 获取用户详细信息
$api->getOrgList(); // 获取服务区列表
$api->getServiceAreaList(); // 获取单位列表
$api->getSiteList(); // 获取站点列表
$api->getUserList(); // 获取用户信息列表
$api->logout(); // 统一登出
```



## 数据同步

### 以编程方式调用

Easy UC 对用户中心的全量同步和增量同步流程做了封装，自动处理数据版本号、数据分块、会话处理等烦恼。

假设已有 UserCenterUserHandler 类，进行同步流程中的回调操作：

```php
use App\Repositories\UserCenterUserHandler;

app()->bind('easyuc.user.handler', UserCenterUserHandler::class);
```

UserCenterUserHandler 根据需要实现以下在 SouthCN\EasyUC\Contracts 命名空间下的接口：

| 接口 | 作用 |
| ---- | ---- |
| ShouldSyncUser     | 声明需要同步用户列表 |
| ShouldSyncUserSites     |   声明需要为用户同步站点权限   |
| ShouldSyncServiceAreas     |   声明需要同步服务区列表   |
| ShouldSyncOrgs     |   声明需要同步机构列表   |
| ShouldSyncSites     |   声明需要同步站点列表   |

绑定 easyuc.user.handler 之后即可开始同步操作：

```php
use SouthCN\EasyUC\Repositories\Sync;

$sync = new Sync;

$sync->sites(true); // 全量同步用户
$sync->sites(false); // 增量同步用户

$sync->users(true); // 全量同步服务区、机构、站点
$sync->users(false); // 增量同步服务区、机构、站点
```



### 通过命令行调用

Easy UC 会自动注册 Artisan 命令到 Laravel，可直接通过命令行进行数据同步：

```bash
# 同步站点列表
php artisan uc:sync-sites # 增量同步（默认）
php artisan uc:sync-sites --full # 全量同步

# 同步用户列表
php artisan uc:sync-users # 增量同步（默认）
php artisan uc:sync-users --full # 全量同步
```



### 通过定时任务调用

通过 Laravel 任务调度功能定时调用同步命令：

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // 定时同步服务区、机构、站点列表
    $schedule->command('uc:sync-sites')
             ->hourly()
             ->withoutOverlapping()
             ->runInBackground();

    // 定时同步用户列表
    $schedule->command('uc:sync-users')
             ->hourly()
             ->withoutOverlapping()
             ->runInBackground();
}
```



### 通过用户中心调用

用户可能会在用户中心手动触发数据同步，因此需要对外暴露 2 条路由分别用于「服务区、机构、站点同步」和「用户同步」。在 Laravel 启动的时候，Easy UC 会自动注册路由，并处理好剩下的事情。

| 用户中心配置        | Easy UC 路由   | 用途                   |
| ------------------- | -------------- | ---------------------- |
| sync_user_url       | /uc/sync-user  | 用户同步               |
| sync_org_struct_url | /uc/sync-sites | 服务区、机构、站点同步 |



## 贡献导引

### 单元测试

Easy UC 覆盖了单元测试，如需进行单元测试，务必在 phpunit.xml 中配置 env：

```xml
<php>
    <env name="UC_APP" value="记得配置"/>
    <env name="UC_TICKET" value="记得配置"/>
    <env name="UC_SITE_APP_ID" value="记得配置"/>
    <env name="UC_OAUTH_TRUSTED_IP" value="记得配置"/>
    <env name="UC_OAUTH_BASE_URL" value="记得配置"/>
</php>
```


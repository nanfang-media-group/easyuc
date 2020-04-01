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

在 Laravel 框架启动阶段，为避免人为失误，Easy UC 会对配置完整性进行自检，如有未配置的必要项，将直接抛出异常。部分配置片段默认已被注释，如需使用被注释的功能，去除注释即可。



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


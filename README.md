# Laravel 5 Dingtalk

Laravel钉钉基础模块，提供钉钉用户资料模型及配置信息

## 安装

1. 通过composer安装
    ```shell
    composer require goodwong/laravel-dingtalk
    ```

4. 打开config/app.php，在providers数组里注册服务：
    ```php
    Goodwong\LaravelDingtalk\DingtalkServiceProvider::class,
    ```


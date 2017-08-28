# Laravel 5 Dingtalk

Laravel钉钉基础模块，提供钉钉用户资料模型及配置信息

> 自动依赖`andersao/l5-repository`模块，但不需要注册
> 自动依赖`guzzlehttp/guzzle`模块

## 安装

1. 通过composer安装
    ```shell
    composer require goodwong/laravel-dingtalk
    ```

4. 打开config/app.php，在providers数组里注册服务：
    ```php
    Goodwong\LaravelDingtalk\DingtalkServiceProvider::class,
    ```



## 事件

1. `Goodwong\LaravelDingtalk\Events\DingtalkUserAuthorized`钉钉授权
    可以监听此事件用于登录系统用户

2. `Goodwong\LaravelDingtalk\Events\DingtalkUserCreated`钉钉用户创建完毕
    可以监听此事件用于同步创建系统用户


## 操作

1. 创建钉钉用户
    ```php
    $creator = app('Goodwong\LaravelDingtalk\Handlers\DingtalkHandler');
    $dingtalkUser = $creator->create($attributes);
    ```

2. 查询钉钉用户
    ```php
    $repository = app('Goodwong\LaravelDingtalk\Repositories\DingtalkUserRepository');
    $dingtalkUser = $repository->find($id);
    $dingtalkUsers = $repository->all();
    // ... 更多参见andersao/l5-repository文档
    ```



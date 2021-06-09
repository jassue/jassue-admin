## 目录结构

```
├─app
│  ├─Domain      业务领域目录
│  │  ├─Common   公共域目录
│  │  │  ├─Helpers      助手类目录
│  │  │  ├─BaseBroadcastEvent.php  基础队列广播事件
│  │  │  ├─BaseEnum.php            基础枚举
│  │  │  ├─BaseQueueListener.php   基础队列监听器
│  │  │  └─BaseRepository.php      基础数据仓库
│  │  └─ ...     更多业务领域目录
│  └─Exceptions
│      ├─BusinessException.php     业务异常类
│      └─ErrorCode.php             错误码
│
├─crontab.sh        任务调度
├─echo-server.nginx.conf     Socket.IO服务器重定向配置文件
├─laravel.supervisor.conf    队列Supervisor配置文件
├─laravel-s.location-php.conf  laravels nginx配置文件
├─laravel-s.location-root.conf laravels nginx配置文件
├─laravel-s.php.conf           laravels nginx配置文件
├─laravel-s.supervisor.conf    laravel Supervisor配置文件
```

## 安装

1、安装相关依赖

```
composer install
```

2、环境变量配置
```
cp .env.example .env
php artisan key:generate
```

3、执行数据库迁移

```
php artisan migrate --seed
```

4、初始化jwt密钥

```
php artisan jwt:secret
```

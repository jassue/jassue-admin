## Jassue Admin
Jassue Admin 是 Laravel8、Vue 实现前后端分离的后台管理系统。此项目为后端代码，前端代码移步 [jassue-admin-frontend](https://github.com/jassue/jassue-admin-frontend)

demo登录入口：https://lva.jassue.cn/admin, 账号：` 18888888888 ` ,  密码：` 123456 `

## 页面展示

![](https://qn.kodo.jassue.cn/jassue-admin/ja1.jpg)

![](https://qn.kodo.jassue.cn/jassue-admin/ja2.jpg)

## 特点

- 前后端完全分离 (互不依赖 开发效率高)
- 内置通讯录组织架构，RBAC权限控制，系统操作日志（节省开发时间）
- 页面权限精确到按钮，API 权限精确到路由
- 集成LaravelS，基于 Swoole 加速（更好的性能）
- Element UI（桌面端组件库）
- 使用 Laravel 8 版本（更多新特性）

## 环境要求

- CentOS >= 7.0
- Nginx >= 1.10
- PHP >= 7.4
- MySQL >= 5.7

## 安装

1、拉取代码，安装相关依赖

```
git clone https://github.com/jassue/jassue-admin.git
composer install
```

2、环境变量配置

```
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan storage:link
```

3、执行数据库迁移

```
php artisan migrate --seed
```

## 使用 docker 安装

- PHP-FPM
  docker-composer.yml:

  ~~~yaml
  version: "3"
  services:
    jassue-admin:
      # Base image documentation: https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/php-nginx.html
      image: jassue/laravel:7.4
      container_name: jassue-admin
      expose:
        - "80"
        - "443"
      ports:
        - "80:80"
        - "443:443"
      environment:
        - WEB_DOCUMENT_ROOT=/app/public
      volumes:
        - ./:/app
        - ./build/laravel.supervisor.conf:/opt/docker/etc/supervisor.d/laravel.conf
  
  networks:
    default:
      driver: bridge
  
  ~~~

- Swoole
  docker-composer.yml:

  ~~~yaml
  version: "3"
  services:
    jassue-admin:
      # Base image documentation: https://dockerfile.readthedocs.io/en/latest/content/DockerImages/dockerfiles/php-nginx.html
      image: jassue/laravel:swoole-7.4 # Development image: jassue/laravel:swoole-dev-7.4
      container_name: jassue-admin
      expose:
        - "80"
        - "443"
      ports:
        - "80:80"
        - "443:443"
      environment:
        - WEB_DOCUMENT_ROOT=/app/public
      volumes:
        - ./:/app
        - ./build/laravel.supervisor.conf:/opt/docker/etc/supervisor.d/laravel.conf
        - ./build/laravel-s.location-root.conf:/opt/docker/etc/nginx/vhost.common.d/10-location-root.conf
        - ./build/laravel-s.php.conf:/opt/docker/etc/nginx/conf.d/10-php.conf
        - ./build/laravel-s.location-php.conf:/opt/docker/etc/nginx/vhost.common.d/10-php.conf
        - ./build/laravel-s.supervisor.conf:/opt/docker/etc/supervisor.d/php-fpm.conf
  
  networks:
    default:
      driver: bridge
  
  ~~~


请确保项目已安装相关依赖，数据库等配置正确，运行以下命令启动项目：

```sh
docker-compose -f docker-compose.yml up -d
```

## License

Apache License Version 2.0 see http://www.apache.org/licenses/LICENSE-2.0.html

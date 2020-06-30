# 快速开始

## 使用 docker 搭建开发环境

使用 [docker](https://yq.aliyun.com/articles/110806) 可以跳过编译环节，快速搭建 tars php 开发环境。
建议使用 docker-compose 启动容器，docker-compose 的配置文件如下：

```yaml
version: '3'

services:
  mysql:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: "pa3sW0rd"
    volumes:
      - ./mysql-data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin" ,"ping", "-h", "localhost"]
      timeout: 20s
      retries: 10
  tars:
    image: wenbinye/tars
    ports:
      - '3000:3000'
      - '3001:3001'
      - 12000:12000
      - 17890:17890
      - 17891:17891
      - 18193:18193
      - 18293:18293
      - 18393:18393
      - 18493:18493
      - 18593:18593
      - 18693:18693
      - 18793:18793
      - 18993:18993
      - 19385:19385      
    environment:
      MYSQL_HOST: 'mysql'
      MYSQL_ROOT_PASSWORD: 'pa3sW0rd'
    links:
      - mysql
    depends_on:
      - mysql
    volumes:
      - ./tars-data:/data/tars
```

使用以下命令启动容器：

```bash
docker-compose up -d
```

启动后打开 http://localhost:3000 。

> 首次打开网址会提示设置 admin 密码。

## 创建项目

使用 tars 脚手架项目创建项目：
```bash
composer create-project -s dev wenbinye/tars-skeleton demo
```

命令执行后会提示以下问题：
```php
  What type of protocol would you like?
  [1] Http
  [2] Tars
  Make your selection (2): 

  Which the psr-4 namespace to use?(demo): 

  What app name?: 
PHPDemo

  What server name?: 
DemoServer
```

首先是项目协议类型，这里我们选择开发基于 tars 协议的 RPC 服务。

第二个问题是项目的命名空间。默认使用当前目录名，根据实际情况进行调整。

第三个和第四个问题是应用名称和服务名称。这个是与下一步部署 tars 服务对应的。

## 服务实现

tars 协议文件在 `tars/servant/` 目录中。使用 `composer gen` 命令生成代码。

> 代码生成需要使用 java，请先安装配置好 java 环境

代码只生成服务接口文件，我们需要按业务需要实现服务。编辑 `src/servant/HelloServantImpl.php` 文件：

```php
<?php

namespace demo\servant;

use kuiper\di\annotation\Service;

/**
 * @Service
 */
class HelloServantImpl implements HelloServant
{
    public function hello($message)
    {
        return "hello, {$message}";
    }
}
```

服务开发完成后使用命令 `composer package` 打包文件，在当前目录会生成类似 `DemoServer_20200630172811.tar.gz` 这样的文件。

## 服务部署

[Tars 官方文档](https://tarscloud.github.io/TarsDocs/hello-world/tarsphp.html) 上有服务部署操作过程。
首先在运维管理 -> 服务部署填写相关部署信息。应用和服务名称与创建项目中设置一致。
服务类型选择 tars_php，模版选择 tars.tarsphp.default，节点选择唯一一个节点。
OBJ 和 HelloServant 中 `@TarsServant` 注解值相同，使用 HelloObj。端口可以点击获取端口按钮自动分配端口。
点击确定。

切换到服务管理页面，左侧应该会显示出刚刚部署的服务。点击 `DemoServer` 选择发布管理，选中节点，点击发布选中节点按钮。
点击上传发布包，选择刚刚打包文件，点击上传。完成后在发布版本下拉框中选中最新的发布版本，点击发布。

发布完成后，在接口调试tab点击添加按钮，上传项目目录下 `tars/servant/hello.tars` 文件。
点击调试，在模块/接口/方法选中要调试的 test/Hello/hello 方法。
下方输入文本框中会显示 JSON 格式参数。填写参数值后点击调试，可以查看接口调用结果。

## Kuiper 框架

项目基于 [Kuiper 框架](https://github.com/wenbinye/kuiper/blob/v0.5.0/docs/index.md)进行开发。Kuiper 框架是一个轻量级框架，是建立在 [Symfony](https://symfony.com/), [PHP-DI](http://php-di.org/)，
[Slim](https://www.slimframework.com/), [Doctrine Annotations](https://github.com/doctrine/annotations) 这些组件基础上。框架在很多方面借鉴 Spring 和 Spring Boot，大量使用注解，简化开发。
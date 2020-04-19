# Tars 程序启动流程

## Tars 应用目录

tars 按照目录默认为 /usr/local/app/tars，以后记为 `$TARSPATH`。

应用日志目录位于 `$TARSPATH/app_log/$APP/$SERVER`，系统应用日志位于 `$TARSPATH/app_log/tars/$server` 目录中。

应用部署目录位于 `$TARSPATH/tarsnode/data/$APP.$SERVER` 目录中。

## 部署目录结构

Tars 程序部署成功后，在 `$TARSPATH/tarsnode/data/$APP.$SERVER` 目录下会有以下文件:

```
`- bin/
 | |- src/
 | |- tars_start.sh
 | `- tars_stop.sh
 |- conf/
 | `- PHPTest.PHPHttpServer.config.conf
 `- data/
  |- manager.pid 
  `- master.pid
```

bin 目录中 `tars_start.sh` 和 `tars_stop.sh` 为 tars 平台自动生成服务启动脚本和服务停止脚本。
bin 目录中其他文件均为发布压缩文件中的内容。

conf 目录是按服务部署模版生成配置文件。

data 目录为 config.conf 配置中的 `datapath`。

## PHP 服务启动脚本

启动脚本优化后如下：
```bash
LOG_PATH=/usr/local/app/tars/app_log/PHPDemo/SimpleHttpServer
APP_PATH=/usr/local/app/tars/tarsnode/data/PHPDemo.SimpleHttpServer

if [ ! -d $LOG_PATH ]; then
    mkdir -p $LOG_PATH
fi

/usr/bin/php $APP_PATH/bin/src/index.php --config=$APP_PATH/conf/PHPDemo.SimpleHttpServer.config.conf start >> $LOG_PATH/PHPDemo.SimpleHttpServer.log 2>&1 
```

实际上就是用 php 运行 `bin/src/index.php` 启动服务。所以项目中必须有 `index.php` 入口文件，其他文件都可以没有。

## 入口文件

入口文件示例如下：

```php
<?php

use wenbinye\tars\server\ServerApplication;

require __DIR__ . '/vendor/autoload.php';

ServerApplication::run();
```

在 ServerApplication 中使用 Symfony Console Application 运行 `wenbinye\tars\server\ServerCommand` 命令。

服务启动过程中，最重要的两个步骤是配置文件的解析和DI容器配置。

## 服务配置



## 健康检查





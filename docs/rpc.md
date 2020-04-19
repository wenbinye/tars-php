# RPC 

## RPC 调用

通过 registry 服务查询服务地址调用 RPC :

```php
<?php
use wenbinye\tars\client\LogServant;
use wenbinye\tars\rpc\TarsClient;
use wenbinye\tars\rpc\route\Route;

$proxy = TarsClient::builder()
    ->setLocator(Route::fromString("tars.tarsregistry.QueryObj@tcp -h 192.168.0.108 -p 17890"))
    ->createProxy(LogServant::class);
$proxy->logger("PHPTest", "PHPHttpServer", "app", "%Y%m%d", ["hello world\n"]);
```

在没有启动 Registry 的情况下，可以通过内存数值查询服务器地址：

```php
<?php

use wenbinye\tars\client\LogServant;
use wenbinye\tars\rpc\route\InMemoryRouteResolver;
use wenbinye\tars\rpc\TarsClient;
use wenbinye\tars\rpc\route\Route;

/** @var LogServant $proxy */
$proxy = TarsClient::builder()
    ->setRouteResolver(new InMemoryRouteResolver([
        Route::fromString("tars.tarslog.LogObj@tcp -h 192.168.0.108 -p 18993")
    ]))
    ->createProxy(LogServant::class);
$proxy->logger("PHPTest", "PHPHttpServer", "app", "%Y%m%d", ["hello world\n"]);
```
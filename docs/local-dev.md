# 本地开发

在使用 `tars-skeleton` 脚手架创建项目时会在项目目录自动生成 `config.conf` 文件，
修改这个配置文件将 client.locator 注释后，程序启动后就不会向 tars 注册中心注册服务，
从而可以本地启动 tars 应用。

修改 `config.conf` 文件如下：

```
		<client>
			asyncthread=3
			# locator=tars.tarsregistry.QueryObj@tcp -h 127.0.0.1 -p 17890
``` 

启动服务：

```bash
php src/index.php --config config.conf
```

## 自动重启

在安装 [fswatch](https://github.com/emcrisostomo/fswatch) 后，可以用这个脚本启动，可以实现修改代码后自动重启。 

```php
#!/bin/bash

function restart() {
    pid=`cat runtime/master.pid`

    if kill -0 $pid 2>/dev/null; then
        kill -9 $pid
    fi

    echo "restart server"
    composer serve &
}

restart

fswatch --event Removed --event Renamed --event Updated --event Created -or -l 3 -0 src/ | while read -d "" event
do
    restart
done
```

## xdebug 调试

swoole 扩展和 xdebug 扩展冲突，启用 swoole 扩展是不能用 xdebug 进行调试。
[kuiper](https://github.com/wenbinye/kuiper) 对 `Swoole\Server` 做了一层封装，
可以用 PHP 的 socket 实现替换 swoole 扩展，从而可以对服务进行调试。

启用 php socket 服务通过修改配置文件：

```
<server>
enable_php_server=1
```

或者在命令行启动时添加参数：

```bash
php src/index.php --config config.conf -D application.enable_php_server=1
```

# 代码生成

[Tars 代码生成](https://github.com/wenbinye/tars-generator) 使用 [TarsJava](https://github.com/TarsCloud/TarsJava.git) 中的
tars 文件解析器，使用[Pebble](https://pebbletemplates.io/) 模板引擎，目前只提供 PHP 代码生成。

通过 composer 安装到项目中：

```bash
composer require --dev wenbinye/tars-gen
```

在 `composer.json` 中添加配置项：

```json
{
  "scripts": {
    "gen": "./vendor/bin/tars-gen"
  }
}
```

运行命令 `composer gen` 即能生成代码。

默认 tars 文件放在项目 `tars` 目录中。当前项目如果提供 Tars RPC 服务，
将定义文件放到 `tars/servant` 目录中，使用其他项目接口，则将 `tars` 
定义文件放到 `tars/client` 目录中。


## composer extra 配置

代码生成器没有任何配置时可能不能正确生成代码。需要在 composer.json 的 extra 配置项中添加相应配置。

配置添加到 composer.json 文件的 `extra.tars.generator` 中。RPC 服务配置放到 `servant` 对应配置中，
客户端配置放到 `client` 配置中。每个配置项都是一个数组，支持配置多组配置。


| 配置项    | 说明                          | client 默认值     | servant 默认值     |
|-----------|-------------------------------|-------------------|--------------------|
| tars_path | tars 文件定义路径             | tars/client       | tars/servant       |
| namespace | 生成代码对应的 php 命名空间   | {psr4}/client     | {psr4}/servant     |
| flat      | 是否忽略 tars 文件中的 module | true              | true               |
| output    | 输出文件路径                  | {psr4Path}/client | {psr4Path}/servant |
| servants  | Interface 与 Servant 名字映射 | 空                | 空                 |

命令行会使用 composer.json 中 `autoload.psr-4` 中第一个名字空间作默认名字空间，对应路径作为默认输出文件路径。

对于 client 必须在配置 Interface 与 Servant 名字映射。配置设置示例：

```json
{
  "extra": {
    "tars": {
      "generator": {
        "client": {
          "servants": {
            "Hello": "TestApp.HelloServer.HelloObj"
          }
        }
      }
    }
  }
}
```


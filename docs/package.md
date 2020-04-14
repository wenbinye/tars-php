# 打包

发布打包通过命令 `composer package` 完成。需要先在 `composer.json` 中添加以下配置：

```json
{
    "scripts": {
        "package": "wenbinye\\tars\\server\\ServerApplication::package"
    },
    "extra": {
        "tars": {
            "serverName": "SimpleHttpServer",
            "manifest": [
                "composer.json",
                {
                    "in": "src"
                },
                {
                    "followLinks": true,
                    "exclude": [
                        "phpunit"
                    ],
                    "in": "vendor"
                }
            ]
        }
    }
}
```

`scripts.package` 用于添加打包命令执行方式。

`extra.tars` 配置打包项目信息，包括：

- serverName 为服务名称
- manifest 为打包到发布文件中的文件清单列表。列表中如果是字符串，则为当前目录下文件名；
  如果是一个数组对象，则将创建 `\Symfony\Component\Finder\Finder` 对象，遍历查找文件，每个配置项将
  调用对应的 finder 对象方法。[参考 symfony finder 文档](https://symfony.com/doc/current/components/finder.html)。
# 配置加载

## 配置文件的解析

在 `ServerCommand` 中首先进行配置文件解析。解析后的配置项以单例模式保存为 `wenbinye\tars\server\Config` 对象，
可以通过 `wenbinye\tars\server\Config::getInstance()` 访问。
解析规则：
- `<tag_name>` 将创建一个 key 为 `tag_name` 的数组，值为 tag 内的配置项。
- `key=value` 配置项按 `=` 拆分成对应数组内的 key, value。key, value 头尾的空格都将清除。
- 以 `#` 开头的行将忽略

## 配置模型对象

配置文件解析后会由解析结果创建两个重要的配置模型对象：服务配置对象和客户端配置对象。
配置模型装配时使用 `@\wenbinye\tars\server\annotation\ConfigItem` 注解。
用于将与属性同名的配置项设置到模型中。`Config` 获取的配置项值是 `string` 类型，
而配置模型中属性值可能非 `string` 类型，需要进行值转换。

值转换的方式可以通过：

- 通过 `set{name}FromString` 方法设置，`{name}` 为对应配置项属性名称
- `@ConfigItem` 的 factory 方法指定
- 通过 setter 方法设置，设置的值会根据 getter 方法返回值类型进行值过滤处理。

### 服务配置 ServerProperties

服务配置对象由方法 `\wenbinye\tars\server\PropertyLoader::loadServerProperties` 创建。
ServerProperties 对象读取配置中 `tars.application.server` 配置项。

### 客户端配置 ClientProperties

客户端配置对象由方法 `\wenbinye\tars\server\PropertyLoader::loadClientProperties` 创建，
读取配置中 `tars.application.client` 配置项。

## 本地配置加载

如果存在 `src/config.php` 文件，文件中定义的配置会加载覆盖配置文件中的值。

## 重要的配置项

除了创建服务配置模型和客户端配置模型之外，以下配置项需要特别关注：

| 配置项                         | 说明                  |
|--------------------------------|-----------------------|
| application.tars.middleware.client  | TARS RPC 客户端中间件 |
| application.tars.middleware.servant | RPC 服务中间件        |
| application.listeners          | 事件监听器            |
| application.tars.connection_options | 连接配置  |
| application.tars.servant_options | 服务连接配置 |

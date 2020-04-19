# Tars 协议

## Tars 数据类型

Tars 数据类型可分为以下类型：

| 数据类型     | 表示方法                |
|--------------|-------------------------|
| 原生数据类型 | int, bool, string 等    |
| Map 类型     | map<keyType, valueType> |
| Vector 类型  | vector<valueType>       |
| Struct 类型  | 自定义类                |

在 `wenbinye\tars\protocol\type` 包中定义了各种类型的 php 类型对象。 

`\wenbinye\tars\protocol\TypeParser` 实现了解析字符串为 php 类型对象的过程。

## Tars 数据序列化和反序列化

php 数据类型在 rpc 调用过程中需要序列化和反序列化，这个过程是通过 [phptars 扩展](https://github.com/TarsPHP/tars-extension.git)实现的。
phptars 扩展提供的接口太复杂， `\wenbinye\tars\protocol\PackerInterface` 接口提供了更简单容易使用的序列化和反序列化接口。

原生类型序列化和反序列化示例如下：

```php
<?php
use kuiper\annotations\AnnotationReader;
use wenbinye\tars\protocol\Packer;

$packer = new Packer(AnnotationReader::getInstance());

$type = $packer->parse('int');
$serialized = $packer->pack($type, '', 42);
$payload = Packer::toPayload('a', $serialized);
echo $packer->unpack($type, 'a', $binary);  // 42
```

和 php 的 `unserialize` 方法有所不同，Packer的 `unpack` 方法封装的是[TUP组包协议](https://github.com/TarsCloud/TarsTup)，
序列化后的结果需要先构造成请求包，然后解析请求包得到请求数据体才能反序列化。

数组类型序列化示例：

```php
<?php

$type = $packer->parse('vector<int>');
$serialized = $packer->pack($type, '', [42]);
$payload = Packer::toPayload('a', $serialized);
$value = $packer->unpack($type, 'a', $binary);  // [42]
```

和原生类型方式完全相同。

自定义类型示例：

```php
<?php
use wenbinye\tars\protocol\annotation\TarsProperty;
use kuiper\annotations\AnnotationReader;
use wenbinye\tars\protocol\Packer;

class Foo
{
    /**
     * @TarsProperty(order = 0, required = true, type = "string")
     *
     * @var string
     */
    public $num;
}
$foo = new Foo();
$foo->num = 42;

$packer = new Packer(AnnotationReader::getInstance());
$type = $packer->parse('Foo');
$data = $packer->pack($type, '', $foo);
$payload = $binary = Packer::toPayload('a', $data);
$value = $packer->unpack($type, 'a', $binary);  // Foo::__set_state(['num' => '42'])
```
对于自定义类型必须使用 `@TarsProperty` 注解标记字段。自定义类型 php 代码一般通过代码生成器
 由 tars 定义文件生成，而不是手写。
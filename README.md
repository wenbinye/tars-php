1. 支持原生 php 类型

```php
<?php
use wenbinye\tars\protocol\TypeParser;

$parser = new TypeParser();
$parser->parse('int', 'ns');
```

<?php

declare(strict_types=1);

namespace wenbinye\tars\server\fixtures;

class HelloService implements HelloServant
{
    /**
     * {@inheritdoc}
     */
    public function hello($message)
    {
        return 'hello '.$message;
    }
}

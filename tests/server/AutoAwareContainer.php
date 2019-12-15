<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use DI\Container;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class AutoAwareContainer extends Container
{
    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        $value = parent::get($id);
        if ($value instanceof LoggerAwareInterface) {
            $value->setLogger($this->get(LoggerInterface::class));
        }

        return $value;
    }
}

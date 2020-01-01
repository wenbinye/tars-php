<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use DI\Definition\Source\DefinitionSource;
use DI\Definition\ValueDefinition;
use wenbinye\tars\server\Config;

class ConfigDefinitionSource implements DefinitionSource
{
    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigDefinitionSource constructor.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(string $name)
    {
        $value = $this->config->get($name);
        if (null !== $value) {
            return new ValueDefinition($value);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitions(): array
    {
        return [];
    }
}

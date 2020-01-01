<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use DI\Definition\ObjectDefinition;
use DI\Definition\Source\AnnotationBasedAutowiring;
use DI\Definition\Source\Autowiring;
use DI\Definition\Source\DefinitionSource;
use DI\Definition\Source\ReflectionBasedAutowiring;

class AwareAutowiring implements DefinitionSource, Autowiring
{
    /**
     * @var ReflectionBasedAutowiring|AnnotationBasedAutowiring
     */
    private $autowiring;

    /**
     * @var AwareInjection[]
     */
    private $awareInjections;

    public function __construct($autowiring, array $awareBeans)
    {
        $this->autowiring = $autowiring;
        $this->awareInjections = $awareBeans;
    }

    public function add(AwareInjection $awareInjection): void
    {
        $this->awareInjections[] = $awareInjection;
    }

    /**
     * {@inheritdoc}
     */
    public function autowire(string $name, ObjectDefinition $definition = null)
    {
        $definition = $this->autowiring->autowire($name, $definition);
        $className = $definition->getClassName();
        foreach ($this->awareInjections as $awareDefinition) {
            if ($awareDefinition->match($className)) {
                $awareDefinition->inject($definition);
            }
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(string $name)
    {
        return $this->autowire($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitions(): array
    {
        return [];
    }
}

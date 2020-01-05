<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use DI\Annotation\Inject;
use DI\Definition\FactoryDefinition;
use DI\Definition\Source\Autowiring;
use DI\Definition\Source\DefinitionArray;
use DI\Definition\Source\DefinitionSource;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use wenbinye\tars\di\annotation\Bean;

class BeanConfigurationSource implements DefinitionSource, AutowiringAwareInterface
{
    /**
     * @var array
     */
    private $configurationBeans;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var DefinitionArray
     */
    private $definitionArray;

    /**
     * @var Autowiring
     */
    private $autowiring;

    /**
     * BeanConfigurationSource constructor.
     */
    public function __construct(array $configurationBeans = [])
    {
        $this->configurationBeans = $configurationBeans;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(string $name)
    {
        return $this->getDefinitionArray()->getDefinition($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitions(): array
    {
        return $this->getDefinitionArray()->getDefinitions();
    }

    public function setAutowiring(Autowiring $autowiring): void
    {
        $this->autowiring = $autowiring;
    }

    public function addConfiguration($configuration): BeanConfigurationSource
    {
        $this->configurationBeans[] = $configuration;

        return $this;
    }

    public function getAnnotationReader()
    {
        if (null === $this->annotationReader) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }

    public function setAnnotationReader(Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    private function getDefinitionArray(): DefinitionArray
    {
        if ($this->definitionArray) {
            return $this->definitionArray;
        }
        $definitions = [];
        foreach ($this->configurationBeans as $configuration) {
            $reflectionClass = new \ReflectionClass($configuration);
            foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                /** @var Bean $beanAnnotation */
                $beanAnnotation = $this->getAnnotationReader()->getMethodAnnotation($method, Bean::class);
                if ($beanAnnotation) {
                    $factoryDefinition = $this->createDefinition($beanAnnotation, $configuration, $method);
                    $definitions[$factoryDefinition->getName()] = $factoryDefinition;
                }
            }
            if ($configuration instanceof DefinitionConfiguration) {
                foreach ($configuration->getDefinitions() as $name => $definition) {
                    $definitions[$name] = $definition;
                }
            }
        }

        return $this->definitionArray = new DefinitionArray($definitions, $this->autowiring);
    }

    private function getMethodParameterInjections(Inject $annotation): array
    {
        $parameters = [];
        foreach ($annotation->getParameters() as $key => $parameter) {
            $parameters[$key] = \DI\get($parameter);
        }

        return $parameters;
    }

    private function createDefinition(Bean $beanAnnotation, $configuration, \ReflectionMethod $method): ?FactoryDefinition
    {
        $name = $beanAnnotation->name;
        if (!$name) {
            if ($method->getReturnType() && !$method->getReturnType()->isBuiltin()) {
                $name = $method->getReturnType()->getName();
            } else {
                $name = $method->getName();
            }
        }
        /** @var Inject $annotation */
        $annotation = $this->getAnnotationReader()->getMethodAnnotation($method, Inject::class);
        if ($annotation) {
            return new FactoryDefinition(
                $name, [$configuration, $method->getName()], $this->getMethodParameterInjections($annotation)
            );
        }

        return new FactoryDefinition($name, [$configuration, $method->getName()]);
    }
}

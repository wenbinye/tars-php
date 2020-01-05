<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use DI\Annotation\Inject;
use DI\Definition\FactoryDefinition;
use DI\Definition\Source\Autowiring;
use DI\Definition\Source\DefinitionArray;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use wenbinye\tars\di\annotation\Bean;

class BeanConfigurationSource extends DefinitionArray
{
    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var array
     */
    private $configurationBeans;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * BeanConfigurationSource constructor.
     */
    public function __construct(array $configurationBeans = [], Autowiring $autowiring = null)
    {
        $this->configurationBeans = $configurationBeans;
        parent::__construct([], $autowiring);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(string $name)
    {
        $this->initialize();

        return parent::getDefinition($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinitions(): array
    {
        $this->initialize();

        return parent::getDefinitions();
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
    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }
        $definitions = [];
        foreach ($this->configurationBeans as $configuration) {
            $reflectionClass = new \ReflectionClass($configuration);
            foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                /** @var Bean $beanAnnotation */
                $beanAnnotation = $this->getAnnotationReader()->getMethodAnnotation($method, Bean::class);
                if ($beanAnnotation) {
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
                        $definitions[$name] = new FactoryDefinition(
                            $name, [$configuration, $method->getName()], $this->getMethodParameterInjections($annotation)
                        );
                    } else {
                        $definitions[$name] = \DI\factory([$configuration, $method->getName()]);
                    }
                }
            }
        }
        $this->addDefinitions($definitions);
        $this->initialized = true;
    }

    private function getMethodParameterInjections(Inject $annotation): array
    {
        $parameters = [];
        foreach ($annotation->getParameters() as $key => $parameter) {
            $parameters[$key] = \DI\get($parameter);
        }

        return $parameters;
    }
}

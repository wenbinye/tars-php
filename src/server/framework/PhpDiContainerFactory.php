<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use kuiper\reflection\ReflectionFileFactory;
use kuiper\reflection\ReflectionNamespaceFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use wenbinye\tars\di\annotation\ComponentInterface;
use wenbinye\tars\di\annotation\ComponentScan;
use wenbinye\tars\di\AnnotationReaderAwareInterface;
use wenbinye\tars\di\AwareAutowiring;
use wenbinye\tars\di\AwareInjection;
use wenbinye\tars\di\BeanConfigurationSource;
use wenbinye\tars\di\BeanConfigurationSourceAwareInterface;
use wenbinye\tars\di\ConfigDefinitionSource;
use wenbinye\tars\di\ContainerBuilder;
use wenbinye\tars\di\ContainerBuilderAwareInterface;
use wenbinye\tars\di\ContainerFactoryInterface;
use wenbinye\tars\server\Config;

class PhpDiContainerFactory implements ContainerFactoryInterface
{
    /**
     * @var BeanConfigurationSource
     */
    private $beanConfigurationSource;

    /**
     * @var ClassLoader
     */
    private $classLoader;

    /**
     * @var AwareAutowiring
     */
    private $autowiring;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * PhpDiContainerFactory constructor.
     */
    public function __construct(?ClassLoader $classLoader = null)
    {
        $this->classLoader = $classLoader;
    }

    public function getBeanConfigurationSource(): BeanConfigurationSource
    {
        if (!$this->beanConfigurationSource) {
            $this->beanConfigurationSource = new BeanConfigurationSource();
        }

        return $this->beanConfigurationSource;
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function getAnnotationReader(): Reader
    {
        if (!$this->annotationReader) {
            AnnotationRegistry::registerLoader('class_exists');
            $this->annotationReader = new AnnotationReader();
        }

        return $this->annotationReader;
    }

    public function setBeanConfigurationSource(BeanConfigurationSource $beanConfigurationSource): void
    {
        $this->beanConfigurationSource = $beanConfigurationSource;
    }

    public function setAutowiring(AwareAutowiring $autowiring): void
    {
        $this->autowiring = $autowiring;
    }

    public function setAnnotationReader(Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    public function getClassLoader(): ClassLoader
    {
        return $this->classLoader;
    }

    public function getContainerBuilder(): ContainerBuilder
    {
        if (!$this->containerBuilder) {
            $this->containerBuilder = new ContainerBuilder();
        }

        return $this->containerBuilder;
    }

    public function setContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;
    }

    public function componentScan(array $namespaces): self
    {
        static $scannedNamespaces;

        $annotationReader = $this->getAnnotationReader();
        $reflectionNamespaceFactory = ReflectionNamespaceFactory::createInstance(ReflectionFileFactory::createInstance())
            ->registerLoader($this->getClassLoader());

        while ($namespaces) {
            $namespace = array_pop($namespaces);
            if (isset($scannedNamespaces[$namespace])) {
                continue;
            }
            foreach ($reflectionNamespaceFactory->create($namespace)->getClasses() as $className) {
                $reflectionClass = new \ReflectionClass($className);
                foreach ($annotationReader->getClassAnnotations($reflectionClass) as $annotation) {
                    if ($annotation instanceof ComponentInterface) {
                        $annotation->setClass($reflectionClass);
                        $this->processComponent($annotation);
                    } elseif ($annotation instanceof ComponentScan) {
                        foreach ($annotation->basePackages ?: [$reflectionClass->getNamespaceName()] as $ns) {
                            $namespaces[] = $ns;
                        }
                    }
                }
            }
            $scannedNamespaces[$namespace] = true;
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function createBuilder(): ContainerBuilder
    {
        $builder = $this->getContainerBuilder();

        $builder->addAwareInjection(AwareInjection::create(LoggerAwareInterface::class));
        $builder->addDefinitions(new ConfigDefinitionSource(Config::getInstance()));
        $builder->addDefinitions($this->getBeanConfigurationSource());

        return $builder;
    }

    public function create(): ContainerInterface
    {
        return $this->createBuilder()->build();
    }

    private function processComponent(ComponentInterface $component): void
    {
        if ($component instanceof AnnotationReaderAwareInterface) {
            $component->setAnnotationReader($this->getAnnotationReader());
        }
        if ($component instanceof ContainerBuilderAwareInterface) {
            $component->setContainerBuilder($this->getContainerBuilder());
        }
        if ($component instanceof BeanConfigurationSourceAwareInterface) {
            $component->setBeanConfigurationSource($this->getBeanConfigurationSource());
        }
        $component->process();
    }
}

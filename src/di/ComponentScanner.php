<?php

declare(strict_types=1);

namespace wenbinye\tars\di;

use Doctrine\Common\Annotations\Reader;
use kuiper\reflection\ReflectionFileFactory;
use kuiper\reflection\ReflectionNamespaceFactory;
use kuiper\reflection\ReflectionNamespaceFactoryInterface;
use wenbinye\tars\di\annotation\ComponentInterface;
use wenbinye\tars\di\annotation\ComponentScan;

class ComponentScanner
{
    /**
     * @var array
     */
    private $scannedNamespaces;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @var ReflectionNamespaceFactoryInterface
     */
    private $reflectionNamespaceFactory;

    /**
     * ComponentScanner constructor.
     *
     * @param Reader $annotationReader
     */
    public function __construct(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;
    }

    public function scan(array $namespaces): void
    {
        $reflectionNamespaceFactory = $this->getReflectionNamespaceFactory();
        while (!empty($namespaces)) {
            $namespace = array_pop($namespaces);
            if (isset($this->scannedNamespaces[$namespace])) {
                continue;
            }
            foreach ($reflectionNamespaceFactory->create($namespace)->getClasses() as $className) {
                $reflectionClass = new \ReflectionClass($className);
                foreach ($this->getAnnotationReader()->getClassAnnotations($reflectionClass) as $annotation) {
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
    }

    private function processComponent(ComponentInterface $component): void
    {
        if ($component instanceof ContainerBuilderAwareInterface) {
            $component->setContainerBuilder($this->containerBuilder);
        }
        $component->process();
    }

    private function getReflectionNamespaceFactory(): ReflectionNamespaceFactory
    {
        if (!$this->reflectionNamespaceFactory) {
            $this->reflectionNamespaceFactory = ReflectionNamespaceFactory::createInstance(ReflectionFileFactory::createInstance())
                ->registerLoader($this->containerBuilder->getClassLoader());
        }

        return $this->reflectionNamespaceFactory;
    }

    private function getAnnotationReader(): Reader
    {
        return $this->containerBuilder->getAnnotationReader();
    }
}

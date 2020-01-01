<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use Composer\Autoload\ClassLoader;
use function DI\autowire;
use function DI\get;
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
use wenbinye\tars\rpc\DefaultErrorHandler;
use wenbinye\tars\rpc\ErrorHandlerInterface;
use wenbinye\tars\rpc\MethodMetadataFactory;
use wenbinye\tars\rpc\MethodMetadataFactoryInterface;
use wenbinye\tars\rpc\RequestFactory;
use wenbinye\tars\rpc\RequestFactoryInterface;
use wenbinye\tars\rpc\RequestIdGenerator;
use wenbinye\tars\rpc\RequestIdGeneratorInterface;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\http\ResponseSender;
use wenbinye\tars\server\http\ResponseSenderInterface;
use wenbinye\tars\server\http\ServerRequestFactoryInterface;
use wenbinye\tars\server\http\ZendDiactorosServerRequestFactory;
use wenbinye\tars\server\ServerInterface;
use wenbinye\tars\server\SwooleServer;
use wenbinye\tars\server\task\Queue;
use wenbinye\tars\server\task\QueueInterface;
use wenbinye\tars\server\task\TaskProcessorInterface;
use wenbinye\tars\support\ContainerFactoryInterface;

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
    public function __construct(ClassLoader $classLoader)
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
        $builder->addDefinitions([
            ServerInterface::class => autowire(SwooleServer::class),
            SwooleServer::class => get(ServerInterface::class),
            QueueInterface::class => autowire(Queue::class),
            TaskProcessorInterface::class => get(QueueInterface::class),
            ServerRequestFactoryInterface::class => autowire(ZendDiactorosServerRequestFactory::class),
            RequestFactoryInterface::class => autowire(RequestFactory::class),
            RequestIdGeneratorInterface::class => autowire(RequestIdGenerator::class),
            ResponseSenderInterface::class => autowire(ResponseSender::class),
            ErrorHandlerInterface::class => autowire(DefaultErrorHandler::class),
            MethodMetadataFactoryInterface::class => autowire(MethodMetadataFactory::class),
        ]);
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

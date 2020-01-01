<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use function DI\autowire;
use DI\ContainerBuilder;
use DI\Definition\Source\AnnotationBasedAutowiring;
use DI\Definition\Source\DefinitionArray;
use function DI\get;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\rpc\MethodMetadataFactory;
use wenbinye\tars\rpc\MethodMetadataFactoryInterface;
use wenbinye\tars\server\annotation\Bean;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\event\BeforeStartEvent;
use wenbinye\tars\server\event\listener\EventListenerInterface;
use wenbinye\tars\server\http\ResponseSender;
use wenbinye\tars\server\http\ResponseSenderInterface;
use wenbinye\tars\server\http\ServerRequestFactoryInterface;
use wenbinye\tars\server\http\ZendDiactorosServerRequestFactory;
use wenbinye\tars\server\PropertyLoader;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\TarsRequestHandler;
use wenbinye\tars\server\ServerInterface;
use wenbinye\tars\server\ServerProperties;
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
     * @var AwareAutowiring
     */
    private $autowiring;

    /**
     * @var array
     */
    private $config;

    /**
     * PhpDiContainerFactory constructor.
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @Bean()
     */
    public function config(): Config
    {
        $config = Config::getInstance();
        $config->merge($this->config);

        return $config;
    }

    /**
     * @Bean
     *
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function annotationReader(): Reader
    {
        AnnotationRegistry::registerLoader('class_exists');

        return new AnnotationReader();
    }

    /**
     * @Bean()
     */
    public function validator(Reader $annotationReader): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAnnotationMapping($annotationReader)
            ->getValidator();
    }

    /**
     * @Bean()
     */
    public function eventDispatcher(ContainerInterface $container, Config $config, LoggerInterface $logger): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(BeforeStartEvent::class, static function () use ($container, $dispatcher, $config, $logger) {
            foreach ($config->get('application.listeners', []) as $event => $listenerId) {
                $logger->debug("attach $listenerId");
                $listener = $container->get($listenerId);
                if ($listener instanceof EventListenerInterface) {
                    $dispatcher->addListener($listener->getSubscribedEvent(), $listener);
                } elseif (is_string($event)) {
                    $dispatcher->addListener($event, $listener);
                } else {
                    throw new \InvalidArgumentException("config application.listeners $listenerId does not bind to any event");
                }
            }
        });

        return $dispatcher;
    }

    /**
     * @Bean()
     */
    public function tarsRequestHandler(ContainerInterface $container, Config $config, MethodMetadataFactoryInterface $methodMetadataFactory): RequestHandlerInterface
    {
        $servants = [];
        $middlewares = [];
        foreach ($config->get('application.servants', []) as $servantId) {
            $servants[] = $container->get($servantId);
        }
        foreach ($config->get('application.servant_middlewares', []) as $middlewareId) {
            $middlewares[] = $container->get($middlewareId);
        }

        return new TarsRequestHandler($servants, $methodMetadataFactory, $middlewares);
    }

    /**
     * @Bean()
     *
     * @throws \wenbinye\tars\support\exception\ValidationException
     */
    public function serverProperties(PropertyLoader $propertyLoader, Config $config): ServerProperties
    {
        $serverProperties = $propertyLoader->loadServerProperties($config);
        $configFile = $serverProperties->getBasePath().'/config.php';
        if (file_exists($configFile)) {
            $config->merge(require $configFile);
        }

        return $serverProperties;
    }

    /**
     * @Bean()
     *
     * @throws \wenbinye\tars\support\exception\ValidationException
     */
    public function clientProperties(PropertyLoader $propertyLoader, Config $config): ClientProperties
    {
        return $propertyLoader->loadClientProperties($config);
    }

    /**
     * @Bean()
     */
    public function logger(): LoggerInterface
    {
        $errorLogHandler = new ErrorLogHandler();
        /** @var LineFormatter $formatter */
        $formatter = $errorLogHandler->getFormatter();
        $formatter->allowInlineLineBreaks();
        $errorLogHandler->setFormatter($formatter);

        return new Logger('test', [$errorLogHandler]);
    }

    /**
     * @Bean()
     */
    public function packer(Reader $annotationReader): PackerInterface
    {
        return new Packer(new TarsTypeFactory($annotationReader));
    }

    public function getBeanConfigurationSource(): BeanConfigurationSource
    {
        if (!$this->beanConfigurationSource) {
            $this->beanConfigurationSource = new BeanConfigurationSource([$this]);
        }

        return $this->beanConfigurationSource;
    }

    public function getAutowiring(): AwareAutowiring
    {
        if (!$this->autowiring) {
            $this->autowiring = new AwareAutowiring(new AnnotationBasedAutowiring(), [
                AwareInjection::create(LoggerAwareInterface::class),
            ]);
        }

        return $this->autowiring;
    }

    /**
     * @throws \Exception
     */
    public function createBuilder(): ContainerBuilder
    {
        $builder = new ContainerBuilder();

        $builder->addDefinitions(new ConfigDefinitionSource(Config::getInstance()));
        $builder->addDefinitions($this->getAutowiring());
        $builder->addDefinitions(new DefinitionArray([
            ServerInterface::class => autowire(SwooleServer::class),
            SwooleServer::class => get(ServerInterface::class),
            QueueInterface::class => autowire(Queue::class),
            TaskProcessorInterface::class => get(QueueInterface::class),
            ServerRequestFactoryInterface::class => autowire(ZendDiactorosServerRequestFactory::class),
            ResponseSenderInterface::class => autowire(ResponseSender::class),
            MethodMetadataFactoryInterface::class => autowire(MethodMetadataFactory::class),
        ], $this->getAutowiring()));
        $builder->addDefinitions($this->getBeanConfigurationSource());

        return $builder;
    }

    public function create(): ContainerInterface
    {
        return $this->createBuilder()->build();
    }
}

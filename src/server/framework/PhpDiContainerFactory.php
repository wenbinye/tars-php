<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use function DI\autowire;
use DI\ContainerBuilder;
use function DI\get;
use Doctrine\Common\Annotations\Reader;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\server\annotation\Bean;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\event\BeforeStartEvent;
use wenbinye\tars\server\event\listener\EventListenerInterface;
use wenbinye\tars\server\PropertyLoader;
use wenbinye\tars\server\ServerInterface;
use wenbinye\tars\server\ServerProperties;
use wenbinye\tars\server\SwooleServer;
use wenbinye\tars\server\task\Queue;
use wenbinye\tars\server\task\QueueInterface;
use wenbinye\tars\support\ContainerFactoryInterface;

class PhpDiContainerFactory implements ContainerFactoryInterface
{
    /**
     * @var BeanConfigurationSource
     */
    private $beanConfigurationSource;

    /**
     * @Bean()
     */
    public function config(): Config
    {
        return Config::getInstance();
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
    public function eventDispatcher(ContainerInterface $container): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(BeforeStartEvent::class, static function () use ($container, $dispatcher) {
            foreach (Config::getInstance()->get('application.listeners', []) as $event => $listenerId) {
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
     *
     * @throws \wenbinye\tars\support\exception\ValidationException
     */
    public function serverProperties(PropertyLoader $propertyLoader, Config $config): ServerProperties
    {
        return $propertyLoader->loadServerProperties($config);
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
        return new Logger('test', [new ErrorLogHandler()]);
    }

    public function getBeanConfigurationSource(): BeanConfigurationSource
    {
        if (!$this->beanConfigurationSource) {
            $this->beanConfigurationSource = new BeanConfigurationSource([$this]);
        }

        return $this->beanConfigurationSource;
    }

    public function createBuilder(): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true)
            ->addDefinitions([
                ServerInterface::class => autowire(SwooleServer::class),
                SwooleServer::class => get(ServerInterface::class),
                QueueInterface::class => autowire(Queue::class),
            ]);
        $builder->addDefinitions($this->getBeanConfigurationSource());
        $builder->addDefinitions(new ConfigDefinitionSource(Config::getInstance()));

        return $builder;
    }

    public function create(): ContainerInterface
    {
        return $this->createBuilder()->build();
    }
}

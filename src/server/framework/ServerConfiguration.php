<?php


namespace wenbinye\tars\server\framework;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\di\annotation\Bean;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\protocol\PackerInterface;
use wenbinye\tars\protocol\TarsTypeFactory;
use wenbinye\tars\server\ClientProperties;
use wenbinye\tars\server\Config;
use wenbinye\tars\server\event\BeforeStartEvent;
use wenbinye\tars\server\event\listener\EventListenerInterface;
use wenbinye\tars\server\PropertyLoader;
use wenbinye\tars\server\rpc\RequestHandlerInterface;
use wenbinye\tars\server\rpc\TarsRequestHandler;
use wenbinye\tars\server\ServerProperties;

class ServerConfiguration
{

    /**
     * @Bean()
     */
    public function config(): Config
    {
        return Config::getInstance();
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
    public function tarsRequestHandler(ContainerInterface $container, Config $config, Reader $reader, PackerInterface $packer): RequestHandlerInterface
    {
        $servants = [];
        $middlewares = [];
        foreach ($config->get('application.servants', []) as $servantId) {
            $servants[] = $container->get($servantId);
        }
        foreach ($config->get('application.servant_middlewares', []) as $middlewareId) {
            $middlewares[] = $container->get($middlewareId);
        }

        return new TarsRequestHandler($servants, $reader, $packer, $middlewares);
    }

    /**
     * @Bean()
     *
     * @throws \wenbinye\tars\support\exception\ValidationException
     */
    public function serverProperties(PropertyLoader $propertyLoader, Config $config): ServerProperties
    {
        $serverProperties = $propertyLoader->loadServerProperties($config);
        $configFile = $serverProperties->getBasePath() . '/config.php';
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
}
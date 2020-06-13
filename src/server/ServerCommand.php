<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Dotenv\Dotenv;
use kuiper\annotations\AnnotationReader;
use kuiper\swoole\exception\ServerStateException;
use kuiper\swoole\listener\ManagerStartEventListener;
use kuiper\swoole\listener\StartEventListener;
use kuiper\swoole\listener\TaskEventListener;
use kuiper\swoole\listener\WorkerStartEventListener;
use kuiper\swoole\server\ServerInterface;
use kuiper\swoole\ServerManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validation;
use Webmozart\Assert\Assert;
use wenbinye\tars\client\ConfigServant;
use wenbinye\tars\rpc\middleware\RequestLog;
use wenbinye\tars\rpc\middleware\SendStat;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\rpc\TarsClient;
use wenbinye\tars\server\listener\WorkerKeepAlive;
use wenbinye\tars\stat\collector\SystemCpuCollector;

class ServerCommand extends Command
{
    public const COMMAND_NAME = 'server';

    /**
     * @var ContainerFactoryInterface|callable
     */
    private $containerFactory;

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->addOption('define', 'D', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'config definition')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'config file')
            ->addArgument('action', InputArgument::OPTIONAL, 'action to perform: start|stop', 'start');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');
        Assert::oneOf($action, ['start', 'stop'], 'Unknown action \'%s\', expected one of: %s');
        $this->loadConfig($input);
        try {
            if ('start' === $action) {
                $this->createContainer()->get(ServerInterface::class)->start();
            } elseif ('stop' === $action) {
                $this->createContainer()->get(ServerManager::class)->stop();
            }

            return 0;
        } catch (ServerStateException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return -1;
        }
    }

    /**
     * @param ContainerFactoryInterface|callable $containerFactory
     */
    public function setContainerFactory($containerFactory): void
    {
        if (!$containerFactory instanceof ContainerFactoryInterface
            && !is_callable($containerFactory)) {
            throw new \InvalidArgumentException('Invalid container factory, expected instance of '.ContainerFactoryInterface::class.', got '.gettype($containerFactory));
        }
        $this->containerFactory = $containerFactory;
    }

    private function createContainer(): ContainerInterface
    {
        if ($this->containerFactory instanceof ContainerFactoryInterface) {
            return $this->containerFactory->create();
        }

        return call_user_func($this->containerFactory);
    }

    private function addDefaultConfig(InputInterface $input): void
    {
        $config = Config::getInstance();
        $config->merge([
            'application' => [
                'monitor' => [
                    'collectors' => [
                        SystemCpuCollector::class,
                    ],
                ],
                'middleware' => [
                    'client' => [
                        SendStat::class,
                        RequestLog::class,
                    ],
                    'servant' => [
                        RequestLog::class,
                    ],
                ],
                'listeners' => [
                    StartEventListener::class,
                    ManagerStartEventListener::class,
                    WorkerStartEventListener::class,
                    TaskEventListener::class,
                    WorkerKeepAlive::class,
                ],
            ],
        ]);
        foreach ($input->getOption('define') as $item) {
            $pair = explode('=', $item, 2);
            $config->set($pair[0], $pair[1] ?? null);
        }
    }

    protected function loadConfig(InputInterface $input): void
    {
        $configFile = $input->getOption('config');
        if (!$configFile) {
            throw new \InvalidArgumentException('config file is required');
        }
        if (!is_readable($configFile)) {
            throw new \InvalidArgumentException("config file '$configFile' is not readable");
        }
        Config::parseFile($configFile);
        $this->addDefaultConfig($input);
        $config = Config::getInstance();
        $propertyLoader = new PropertyLoader(AnnotationReader::getInstance(), Validation::createValidatorBuilder()->getValidator());
        $serverProperties = $propertyLoader->loadServerProperties($config);
        $env = $config->getString('tars.application.server.env_config_file');
        if ($env) {
            /** @var ConfigServant $configServant */
            $configServant = TarsClient::builder()
                ->setLocator(Route::fromString($config->getString('tars.application.client.locator')))
                ->createProxy(ConfigServant::class);
            $ret = $configServant->loadConfig($serverProperties->getApp(), $serverProperties->getServer(), $env, $content);
            if (0 === $ret) {
                file_put_contents($serverProperties->getBasePath().'/'.$env, $content);
            }
            if (class_exists(Dotenv::class)) {
                Dotenv::createImmutable($serverProperties->getBasePath(), [$env, '.env'], false)->safeLoad();
            }
        }
        $configFile = $serverProperties->getSourcePath().'/config.php';
        if (file_exists($configFile)) {
            /* @noinspection PhpIncludeInspection */
            $config->merge(require $configFile);
        }
    }
}

<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Dotenv\Dotenv;
use kuiper\annotations\AnnotationReader;
use kuiper\helper\Properties;
use kuiper\swoole\listener\ManagerStartEventListener;
use kuiper\swoole\listener\StartEventListener;
use kuiper\swoole\listener\TaskEventListener;
use kuiper\swoole\listener\WorkerStartEventListener;
use kuiper\swoole\monolog\CoroutineIdProcessor;
use kuiper\web\middleware\AccessLog;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Validator\Validation;
use wenbinye\tars\client\ConfigServant;
use wenbinye\tars\rpc\middleware\AddRequestReferer;
use wenbinye\tars\rpc\middleware\ErrorHandler;
use wenbinye\tars\rpc\middleware\RequestLog;
use wenbinye\tars\rpc\middleware\Retry;
use wenbinye\tars\rpc\middleware\SendStat;
use wenbinye\tars\rpc\middleware\ServerRequestLog;
use wenbinye\tars\rpc\route\Route;
use wenbinye\tars\rpc\TarsClient;
use wenbinye\tars\server\listener\ReopenLogFile;
use wenbinye\tars\server\listener\WorkerKeepAlive;
use wenbinye\tars\stat\collector\ServiceMemoryCollector;
use wenbinye\tars\stat\collector\WorkerNumCollector;

class ConfigLoader implements ConfigLoaderInterface
{
    private const REGEXP_PLACEHOLDER = '#\{([^\{\}]+)\}#';

    /**
     * @var PropertyLoader
     */
    private $propertyLoader;

    /**
     * @var ?string
     */
    private $envFilePath;

    /**
     * ConfigLoader constructor.
     *
     * @param PropertyLoader|null $propertyLoader
     * @param string|null         $envFile
     */
    public function __construct(?PropertyLoader $propertyLoader = null, string $envFile = null)
    {
        $this->propertyLoader = $propertyLoader ?? new PropertyLoader(
                AnnotationReader::getInstance(),
                Validation::createValidatorBuilder()
                    ->enableAnnotationMapping(AnnotationReader::getInstance())
                    ->getValidator()
            );
        $this->envFilePath = $envFile;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $configFile, array $properties = []): void
    {
        if (!is_readable($configFile)) {
            throw new \InvalidArgumentException("config file '$configFile' is not readable");
        }
        Config::parseFile($configFile);
        $config = Config::getInstance();
        $serverProperties = $this->propertyLoader->loadServerProperties($config);
        $this->addDefaultConfig($config, $serverProperties);
        $this->addCommandLineOptions($config, $properties);
        $this->loadEnvFile($config, $serverProperties);
        $configFile = $serverProperties->getSourcePath().'/config.php';
        if (file_exists($configFile)) {
            /* @noinspection PhpIncludeInspection */
            $config->merge(require $configFile);
        }
        $this->expandConfig($config, static function (array $matches) use ($config) {
            $name = $matches[1];
            if (!$config->has($name)) {
                throw new \RuntimeException("Unknown config entry: '$name'");
            }

            return $config->get($name);
        });
        $this->addDefaultLoggers($config, $serverProperties);
    }

    private function addCommandLineOptions(Properties $config, array $properties): void
    {
        foreach ($properties as $i => $value) {
            if (!strpos($value, 'application.')) {
                $value = 'application.'.$value;
                $properties[$i] = $value;
            }
        }
        $define = parse_ini_string(implode("\n", $properties));
        if (is_array($define)) {
            foreach ($define as $key => $value) {
                $config->set($key, $value ?? null);
            }
        }
    }

    private function addDefaultConfig(Properties $config, ServerProperties $serverProperties): void
    {
        $config->merge([
            'application' => [
                'name' => $serverProperties->getServerName(),
                'base-path' => $serverProperties->getBasePath(),
                'server' => [
                    'enable-php-server' => $config->getBool('tars.application.server.enable_php_server', false),
                ],
                'listeners' => [
                    StartEventListener::class,
                    ManagerStartEventListener::class,
                    WorkerStartEventListener::class,
                    TaskEventListener::class,
                    ReopenLogFile::class,
                    WorkerKeepAlive::class,
                ],
                'web' => [
                    'middleware' => [
                        AccessLog::class,
                    ],
                ],
                'tars' => [
                    'config-file' => $config->get('tars.application.server.env_file'),
                    'middleware' => [
                        'client' => [
                            RequestLog::class,
                            ErrorHandler::class,
                            AddRequestReferer::class,
                            SendStat::class,
                            Retry::class,
                        ],
                        'servant' => [
                            ServerRequestLog::class,
                        ],
                    ],
                    'collectors' => [
                        ServiceMemoryCollector::class,
                        WorkerNumCollector::class,
                    ],
                ],
                'logging' => [
                    'path' => $serverProperties->getAppLogPath(),
                    'logger' => [
                        ServerRequestLog::class => 'TarsServerAccessLogger',
                        RequestLog::class => 'TarsClientAccessLogger',
                        AccessLog::class => 'WebAccessLogger',
                    ],
                    'level' => [
                        'wenbinye\\tars' => 'info',
                        'kuiper\\swoole' => 'info',
                    ],
                ],
            ],
        ]);
    }

    protected function loadEnvFile(Properties $config, ServerProperties $serverProperties): void
    {
        if (!class_exists(Dotenv::class)) {
            return;
        }
        $envFiles = ['.env'];
        $env = $config->getString('application.tars.config-file');
        if ('' !== $env) {
            $localFile = $serverProperties->getBasePath().'/'.$env;
            /** @var ConfigServant $configServant */
            $configServant = TarsClient::builder()
                ->setLocator(Route::fromString($config->getString('tars.application.client.locator')))
                ->createProxy(ConfigServant::class);
            $ret = $configServant->loadConfig($serverProperties->getApp(), $serverProperties->getServer(), $env, $content);
            if (0 === $ret && !empty($content)) {
                file_put_contents($localFile, $content);
            }
            if (is_readable($localFile)) {
                array_unshift($envFiles, $env);
            }
        }
        Dotenv::createImmutable($this->envFilePath ?? $serverProperties->getBasePath(), $envFiles, false)
            ->safeLoad();
    }

    protected function expandConfig(Properties $config, callable $replacer): void
    {
        $re = self::REGEXP_PLACEHOLDER;
        foreach ($config as $key => $value) {
            if (is_string($value) && preg_match(self::REGEXP_PLACEHOLDER, $value)) {
                do {
                    $value = preg_replace_callback($re, $replacer, $value);
                } while (preg_match(self::REGEXP_PLACEHOLDER, $value));

                $config[$key] = $value;
            } elseif ($value instanceof Properties) {
                $this->expandConfig($value, $replacer);
            }
        }
    }

    protected function addDefaultLoggers(Properties $config, ServerProperties $serverProperties): void
    {
        $config->set('application.logging.loggers', array_merge([
            'root' => $this->createRootLogger($config, $serverProperties),
            'WebAccessLogger' => $this->createAccessLogger($serverProperties->getAppLogPath().'/access.log'),
            'TarsServerAccessLogger' => $this->createAccessLogger($serverProperties->getAppLogPath().'/tars-server.log'),
            'TarsClientAccessLogger' => $this->createAccessLogger($serverProperties->getAppLogPath().'/tars-client.log'),
        ], $config->get('application.logging.loggers', [])));
    }

    private function createAccessLogger(string $logFileName): array
    {
        return [
            'handlers' => [
                [
                    'handler' => [
                        'class' => StreamHandler::class,
                        'constructor' => [
                            'stream' => $logFileName,
                        ],
                    ],
                    'formatter' => [
                        'class' => LineFormatter::class,
                        'constructor' => [
                            'format' => "%message% %context% %extra%\n",
                        ],
                    ],
                ],
            ],
            'processors' => [
                CoroutineIdProcessor::class,
            ],
        ];
    }

    /**
     * @param Properties       $config
     * @param ServerProperties $serverProperties
     *
     * @return array
     */
    protected function createRootLogger(Properties $config, ServerProperties $serverProperties): array
    {
        $loggerLevelName = strtoupper($serverProperties->getLogLevel());

        $loggerLevel = constant(Logger::class.'::'.$loggerLevelName);
        if (!isset($loggerLevel)) {
            throw new \InvalidArgumentException("Unknown logger level '{$loggerLevelName}'");
        }
        $handlers = [];
        if ($config->getBool('tars.application.server.enable_console_logging', true)) {
            $handlers[] = [
                'handler' => [
                    'class' => StreamHandler::class,
                    'constructor' => [
                        'stream' => 'php://stderr',
                        'level' => $loggerLevel,
                    ],
                ],
                'formatter' => [
                    'class' => LineFormatter::class,
                    'constructor' => [
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ];
        }
        $handlers[] = [
            'handler' => [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => sprintf('%s/default.log', $serverProperties->getAppLogPath()),
                    'level' => $loggerLevel,
                ],
            ],
        ];
        $handlers[] = [
            'handler' => [
                'class' => StreamHandler::class,
                'constructor' => [
                    'stream' => sprintf('%s/error.log', $serverProperties->getAppLogPath()),
                    'level' => Logger::ERROR,
                ],
            ],
        ];

        return [
            'name' => $serverProperties->getServer(),
            'handlers' => $handlers,
            'processors' => [
                CoroutineIdProcessor::class,
            ],
        ];
    }
}

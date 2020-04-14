<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use kuiper\annotations\AnnotationReaderInterface;
use kuiper\helper\Text;
use kuiper\reflection\ReflectionType;
use kuiper\swoole\SwooleSetting;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\exception\ValidationException;
use wenbinye\tars\server\annotation\ConfigItem;

class PropertyLoader
{
    private const ADAPTER_SUFFIX = 'Adapter';
    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * PropertyLoader constructor.
     */
    public function __construct(AnnotationReaderInterface $annotationReader, ValidatorInterface $validator)
    {
        $this->annotationReader = $annotationReader;
        $this->validator = $validator;
    }

    public function loadClientProperties(Config $config): ClientProperties
    {
        $clientProperties = new ClientProperties();
        $this->load($clientProperties, $config->tars->application->client);

        $errors = $this->validator->validate($clientProperties);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        return $clientProperties;
    }

    public function loadServerProperties(Config $config): ServerProperties
    {
        $serverProperties = new ServerProperties();
        /** @var Config $serverConfig */
        $serverConfig = $config->tars->application->server;
        $this->load($serverProperties, $serverConfig);
        $adapters = [];
        $swooleServerSettings = [];
        foreach ($serverConfig as $key => $value) {
            if (Text::endsWith($key, self::ADAPTER_SUFFIX)
                && Text::startsWith($key, $serverProperties->getServerName().'.')) {
                $adapterName = substr($key, strlen($serverProperties->getServerName()) + 1, -strlen(self::ADAPTER_SUFFIX));
                $adapters[$adapterName] = $adapterProperties = new AdapterProperties();
                $adapterProperties->setName($adapterName);
                $this->load($adapterProperties, $value);
                $errors = $this->validator->validate($adapterProperties);
                if ($errors->count() > 0) {
                    throw new ValidationException($errors);
                }
            } elseif (SwooleSetting::hasValue($key)) {
                $swooleServerSettings[$key] = ReflectionType::forName(SwooleSetting::fromValue($key)->type)->sanitize($value);
            }
        }
        if (empty($swooleServerSettings[SwooleSetting::TASK_WORKER_NUM])) {
            // at least one task worker
            $swooleServerSettings[SwooleSetting::TASK_WORKER_NUM] = 1;
        }
        $serverProperties->setAdapters($adapters);
        $serverProperties->setSwooleSettings($swooleServerSettings);
        $errors = $this->validator->validate($serverProperties);
        if ($errors->count() > 0) {
            throw new ValidationException($errors);
        }

        return $serverProperties;
    }

    /**
     * @param object $properties
     *
     * @throws \ReflectionException
     */
    public function load($properties, Config $config): void
    {
        $reflectionClass = new \ReflectionClass($properties);
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $getter = 'get'.$property->getName();
            if (!$reflectionClass->hasMethod($getter)) {
                continue;
            }
            /** @var ConfigItem $configItem */
            $configItem = $this->annotationReader->getPropertyAnnotation($property, ConfigItem::class);
            if (!$configItem) {
                continue;
            }
            $value = $this->readConfigValue($config, $configItem, $property);
            if (!isset($value)) {
                continue;
            }
            // to set 'foo' config item value try:
            // 1. method setFooFromString
            // 2. annotation ConfigItem->factory callback
            // 3. method setFoo
            $stringSetter = sprintf('set%sFromString', $property->getName());
            if ($reflectionClass->hasMethod($stringSetter)) {
                if ($configItem->factory) {
                    trigger_error(sprintf("Property '%s' of '%s' setter '%s' override factory method", get_class($properties), $property->getName(), $stringSetter));
                }
                $reflectionClass->getMethod($stringSetter)->invoke($properties, $value);
            } else {
                if ($configItem->factory) {
                    if (false !== strpos($configItem->factory, '::')) {
                        $value = call_user_func(explode('::', $configItem->factory, 2), $value);
                    } elseif (method_exists(get_class($properties), $configItem->factory)) {
                        $value = call_user_func([get_class($properties), $configItem->factory], $value);
                    } else {
                        $value = call_user_func($configItem->factory, $value);
                    }
                } else {
                    $type = $reflectionClass->getMethod($getter)->getReturnType();
                    $value = ReflectionType::forName((string) $type)->sanitize($value);
                }
                $reflectionClass->getMethod('set'.$property->getName())->invoke($properties, $value);
            }
        }
    }

    /**
     * @return string
     */
    private function readConfigValue(Config $config, ConfigItem $configItem, \ReflectionProperty $property): ?string
    {
        foreach ([$configItem->name,
                     $property->name,
                     strtolower($property->name),
                     Text::snakeCase($property->name, '-'),
                     str_replace('ServantName', '', $property->name),
                 ] as $candidate) {
            if (isset($candidate, $config[$candidate])) {
                return $config[$candidate];
            }
        }

        return null;
    }
}

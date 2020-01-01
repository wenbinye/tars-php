<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use wenbinye\tars\server\annotation\ConfigItem;
use wenbinye\tars\support\exception\ValidationException;
use wenbinye\tars\support\Text;
use wenbinye\tars\support\Type;

class PropertyLoader
{
    const ADAPTER_SUFFIX = 'Adapter';
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * PropertyLoader constructor.
     */
    public function __construct(Reader $annotationReader, ValidatorInterface $validator)
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
            } elseif (SwooleServerSetting::hasValue($key)) {
                $swooleServerSettings[$key] = Type::fromString(SwooleServerSetting::fromValue($key)->type, (string) $value);
            }
        }
        if (empty($swooleServerSettings[SwooleServerSetting::TASK_WORKER_NUM])) {
            // at least one task worker
            $swooleServerSettings[SwooleServerSetting::TASK_WORKER_NUM] = 1;
        }
        $serverProperties->setAdapters($adapters);
        $serverProperties->setSwooleServerSettings($swooleServerSettings);
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
            $value = null;
            foreach ([$configItem->name,
                         $property->name,
                         strtolower($property->name),
                         Text::snakeCase($property->name, '-'),
                         str_replace('ServantName', '', $property->name),
                     ] as $candidate) {
                if (isset($candidate, $config[$candidate])) {
                    $value = $config[$candidate];
                    break;
                }
            }
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
                        $value = call_user_func(explode('::', 2), $value);
                    } else {
                        $value = call_user_func([(string) $reflectionClass->getMethod($getter)->getReturnType(), $configItem->factory], $value);
                    }
                } else {
                    $type = $reflectionClass->getMethod($getter)->getReturnType();
                    $value = Type::fromString($type, (string) $value);
                }
                $reflectionClass->getMethod('set'.$property->getName())->invoke($properties, $value);
            }
        }
    }
}

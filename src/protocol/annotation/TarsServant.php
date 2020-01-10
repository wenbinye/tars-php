<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol\annotation;

use kuiper\di\annotation\ComponentInterface;
use kuiper\di\annotation\ComponentTrait;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class TarsServant implements ComponentInterface
{
    use ComponentTrait;

    private static $SERVANT_INTERFACES;

    /**
     * @var string
     */
    public $name;

    public static function getServantInterface(string $servantName): ?string
    {
        return self::$SERVANT_INTERFACES[$servantName] ?? null;
    }

    public static function register(string $servantName, string $servantClass): void
    {
        self::$SERVANT_INTERFACES[$servantName] = $servantClass;
    }

    public function handle(): void
    {
        self::$SERVANT_INTERFACES[$this->name] = $this->class->getName();
    }
}

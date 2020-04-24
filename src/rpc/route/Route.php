<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Symfony\Component\Validator\Constraints as Assert;

class Route
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $servantName;

    /**
     * @Assert\Count(min=1)
     *
     * @var ServerAddress[]
     */
    private $addresses;

    /**
     * ServantRoute constructor.
     *
     * @param ServerAddress[] $routeList
     */
    public function __construct(string $servantName, array $routeList)
    {
        $this->servantName = $servantName;
        $this->addresses = $routeList;
    }

    public function getServantName(): string
    {
        return $this->servantName;
    }

    public function getSize(): int
    {
        return count($this->addresses);
    }

    public function isEmpty(): bool
    {
        return empty($this->addresses);
    }

    /**
     * @return ServerAddress[]
     */
    public function getAddressList(): array
    {
        return $this->addresses;
    }

    public static function fromString(string $str): Route
    {
        $pos = strpos($str, '@');
        if (false === $pos) {
            throw new \InvalidArgumentException("No servant name in '$str'");
        }
        $servantName = substr($str, 0, $pos);
        $str = substr($str, $pos + 1);

        return new self($servantName, array_map([ServerAddress::class, 'fromString'], explode(':', $str)));
    }
}

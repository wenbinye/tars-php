<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class Parameters implements ParametersInterface
{
    /**
     * @var string
     */
    private $scheme;
    /**
     * @var string
     */
    private $host;
    /**
     * @var int
     */
    private $port;

    /**
     * Parameters constructor.
     */
    public function __construct(string $scheme, string $host, int $port)
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function __toString()
    {
        return sprintf('%s://%s:%s', $this->scheme, $this->host, $this->port);
    }
}

<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\route;

use Symfony\Component\Validator\Constraints as Assert;

final class ServerAddress
{
    /**
     * @Assert\Choice(choices={"tcp", "udp"})
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $protocol;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $host;

    /**
     * @Assert\Range(min=1, max=65536)
     *
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var int
     */
    private $weight;

    /**
     * @var bool
     */
    private $encrypted;

    /**
     * @var string[]
     */
    private static $SHORT_OPTIONS = [
        'host' => 'h',
        'port' => 'p',
        'timeout' => 't',
        'encrypted' => 'e',
    ];

    /**
     * constructor.
     */
    public function __construct(string $protocol, string $host, int $port, int $timeout, int $weight = 100, bool $encrypted = false)
    {
        $this->protocol = $protocol;
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout > 0 ? $timeout : 20000;
        $this->weight = $weight;
        $this->encrypted = $encrypted;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function withProtocol(string $protocol): self
    {
        $new = clone $this;
        $new->protocol = $protocol;

        return $new;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function withHost(string $host): self
    {
        $new = clone $this;
        $new->host = $host;

        return $new;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function withPort(int $port): self
    {
        $new = clone $this;
        $new->port = $port;

        return $new;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function withTimeout(int $timeout): self
    {
        $new = clone $this;
        $new->timeout = $timeout;

        return $new;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function withWeight(int $weight): self
    {
        $new = clone $this;
        $new->weight = $weight;

        return $new;
    }

    public function isEncrypted(): bool
    {
        return $this->encrypted;
    }

    public function withEncrypted(bool $encrypted): self
    {
        $new = clone $this;
        $new->encrypted = $encrypted;

        return $new;
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this));
    }

    public function getAddress(): string
    {
        return $this->host.':'.$this->port;
    }

    public function __toString()
    {
        $str = '';

        return $str.$this->protocol.' '.implode(' ', array_filter(array_map(function ($name): ?string {
            if ('encrypted' === $name) {
                return $this->encrypted ? '-e' : '';
            }

            return isset($this->{$name}) ? '-'.self::$SHORT_OPTIONS[$name].' '.$this->{$name} : null;
        }, array_keys(self::$SHORT_OPTIONS))));
    }

    public static function create(string $host, int $port): self
    {
        return new self('tcp', $host, $port, 0);
    }

    public static function fromAddress(string $str): self
    {
        [$host, $port] = explode(':', $str);

        return new self('tcp', $host, (int) $port, 0);
    }

    public static function fromString(string $str): self
    {
        $address = [
            'protocol' => '',
            'host' => '',
            'port' => 0,
            'timeout' => 0,
            'weight' => 100,
            'encrypted' => false,
        ];
        $parts = preg_split("/\s+/", $str);
        $address['protocol'] = array_shift($parts);
        while (!empty($parts)) {
            $opt = array_shift($parts);
            if (0 === strpos($opt, '-')) {
                $name = array_search(substr($opt, 1), self::$SHORT_OPTIONS, true);
                if (false === $name) {
                    continue;
                }
                if ('encrypted' === $name) {
                    $address[$name] = true;
                    continue;
                }
                $value = array_shift($parts);
                if (in_array($name, ['port', 'timeout'], true)) {
                    $address[$name] = (int) $value;
                } else {
                    $address[$name] = $value;
                }
            }
        }

        if (!in_array($address['protocol'], ['tcp', 'udp'], true)) {
            throw new \InvalidArgumentException("invalid address protocol: original text is '$str'");
        }
        if (empty($address['host'])) {
            throw new \InvalidArgumentException("invalid address host: original text is '$str'");
        }
        if ($address['port'] < 1 || $address['port'] > 65536) {
            throw new \InvalidArgumentException("invalid address port: original text is '$str'");
        }

        return new static($address['protocol'], $address['host'], $address['port'], $address['timeout'],
            $address['weight'], $address['encrypted']);
    }
}

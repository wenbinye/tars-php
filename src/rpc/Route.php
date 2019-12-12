<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

class Route
{
    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var string
     */
    private $servantName;

    private static $SHORT_OPTIONS = [
        'host' => 'h',
        'port' => 'p',
        'timeout' => 't',
    ];

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getServantName(): string
    {
        return $this->servantName;
    }

    public function toArray()
    {
        return array_filter(get_object_vars($this));
    }

    public function __toString()
    {
        $str = '';
        if ($this->servantName) {
            $str = $this->servantName.'@';
        }

        return $str.$this->protocol.' '.implode(' ', array_filter(array_map(function ($name) {
            return isset($this->{$name}) ? '-'.self::$SHORT_OPTIONS[$name].' '.$this->{$name} : null;
        }, array_keys(self::$SHORT_OPTIONS))));
    }

    public static function fromString(string $str): Route
    {
        $route = new static();
        $route->servantName = '';
        $pos = strpos($str, '@');
        if (false !== $pos) {
            $route->servantName = substr($str, 0, $pos);
            $str = substr($str, $pos + 1);
        }
        $parts = preg_split("/\s+/", $str);
        $route->protocol = array_shift($parts);
        while (!empty($parts)) {
            $opt = array_shift($parts);
            if (0 === strpos($opt, '-')) {
                $name = array_search(substr($opt, 1), self::$SHORT_OPTIONS, true);
                if (false !== $name) {
                    $value = array_shift($parts);
                    if (in_array($name, ['port', 'timeout'], true)) {
                        $route->{$name} = (int) $value;
                    } else {
                        $route->{$name} = $value;
                    }
                }
            }
        }

        return $route;
    }
}

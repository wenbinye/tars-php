<?php

declare(strict_types=1);

namespace wenbinye\tars\deploy;

use kuiper\helper\Arrays;
use kuiper\helper\Text;
use Symfony\Component\Finder\Finder;

class PackageConfig
{
    /**
     * @var string
     */
    private $serverName;
    /**
     * @var Finder[]
     */
    private $finders;
    /**
     * @var string
     */
    private $basePath;

    public function __construct(string $basePath, array $options)
    {
        $options = Arrays::mapKeys($options, static function ($key) {
            return Text::snakeCase($key, '-');
        });
        $this->basePath = rtrim($basePath, '/');
        $this->serverName = $options['server-name'];
        $this->finders[0] = [];
        foreach ($options['manifest'] as $methods) {
            if (is_string($methods)) {
                $this->finders[0][] = new \SplFileInfo($this->getCanonicalPath($methods));
            } elseif (is_array($methods)) {
                $this->finders[] = $this->createFinder($methods);
            }
        }
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getServerName(): string
    {
        return $this->serverName;
    }

    /**
     * @return Finder[]
     */
    public function getFinders(): array
    {
        return $this->finders;
    }

    private function createFinder(array $methods): Finder
    {
        $finder = Finder::create()
            ->files()
            ->ignoreVCS(true);

        if (isset($methods['in'])) {
            $methods['in'] = array_map(function ($path) {
                return $this->getCanonicalPath($path);
            }, (array) $methods['in']);
        }

        foreach ($methods as $method => $arguments) {
            if (false === method_exists($finder, $method)) {
                throw new \InvalidArgumentException(sprintf('The method "Finder::%s" does not exist.', $method));
            }

            $arguments = (array) $arguments;

            foreach ($arguments as $argument) {
                $finder->$method($argument);
            }
        }

        return $finder;
    }

    private function getCanonicalPath($path): string
    {
        return $this->basePath.'/'.ltrim($path, '/');
    }
}

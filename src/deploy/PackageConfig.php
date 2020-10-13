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
     * @var array
     */
    private $finders;
    /**
     * @var array<string,bool>
     */
    private $files;
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var array
     */
    private static $DEFAULTS = [
        'src' => [],
        'resources' => [],
        'vendor' => [
            'followLinks' => true,
            'exclude' => [
                'phpunit',
                'mockery',
                'hamcrest',
                'php-cs-fixer',
                'vendor',
                'tars-gen',
            ],
        ],
    ];

    public function __construct(string $basePath, array $options)
    {
        $options = Arrays::mapKeys($options, static function ($key): string {
            return Text::snakeCase($key, '-');
        });
        $this->basePath = rtrim($basePath, '/');
        $this->serverName = $options['server-name'];
        $this->finders[0] = [];
        $this->addFile('composer.json');
        $defaults = self::$DEFAULTS;
        foreach ($options['manifest'] ?? [] as $item) {
            if (is_string($item)) {
                $this->addFile($item);
            } elseif (is_array($item)) {
                if (isset($item['in'])) {
                    unset($defaults[$item['in']]);
                }
                $this->finders[] = $this->createFinder($item);
            }
        }
        foreach ($defaults as $dir => $item) {
            if (is_dir($dir)) {
                $this->finders[] = $this->createFinder(array_merge(['in' => $dir], $item));
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
     * @return array<\Iterator<\SplFileInfo>>
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
            $methods['in'] = array_map(function (string $path): string {
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

    /**
     * @param string $path
     *
     * @return string
     */
    private function getCanonicalPath(string $path): string
    {
        return $this->basePath.'/'.ltrim($path, '/');
    }

    private function addFile(string $fileName): void
    {
        if (isset($this->files[$fileName])) {
            return;
        }
        $this->files[$fileName] = true;
        $this->finders[0][] = new \SplFileInfo($this->getCanonicalPath($fileName));
    }
}

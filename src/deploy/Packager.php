<?php

declare(strict_types=1);

namespace wenbinye\tars\deploy;

use Symfony\Component\Filesystem\Filesystem;

class Packager
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * Packager constructor.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public static function package(): void
    {
        (new static(self::detectProjectPath()))->execute();
    }

    public function execute(): string
    {
        $config = $this->readConfig();
        $filesystem = new Filesystem();

        $tempnam = tempnam(sys_get_temp_dir(), 'tars-build');
        @unlink($tempnam);
        $dir = $tempnam.'/'.$config->getServerName().'/src';
        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create temporary directory $dir");
        }
        $basePathLen = strlen($this->basePath);
        $n = 0;
        foreach ($config->getFinders() as $finder) {
            foreach ($finder as $file) {
                /** @var \SplFileInfo $file */
                $file = (string) $file;
                $relPath = substr($file, $basePathLen);
                // error_log("copy $relPath to ${dir}$relPath");
                ++$n;
                if (0 === $n % 100) {
                    error_log("copy $n files to $dir");
                }
                $filesystem->copy($file, $dir.$relPath);
            }
        }
        // 检查 index.php 是否存在
        if (!file_exists($dir.'/index.php')) {
            throw new \RuntimeException("the entrance file $this->basePath/index.php does not exist: $dir");
        }

        //打包
        $tgzFile = $this->basePath.'/'.sprintf('%s_%s.tar.gz', $config->getServerName(), date('YmdHis'));
        $phar = new \PharData($tgzFile);
        $phar->compress(\Phar::GZ);
        $phar->buildFromDirectory($tempnam);
        $filesystem->remove($tempnam);

        error_log("create package $tgzFile");

        return $tgzFile;
    }

    private static function detectProjectPath(): string
    {
        $dir = getcwd();
        while (!file_exists($dir.'/composer.json')) {
            $parentDir = dirname($dir);
            if ($parentDir === $dir) {
                throw new \InvalidArgumentException('Cannot detect project path, is there composer.json in current directory?');
            }
            $dir = $parentDir;
        }

        return $dir;
    }

    private function readConfig(): PackageConfig
    {
        $composerJson = $this->basePath.'/composer.json';
        if (!is_readable($composerJson)) {
            throw new \InvalidArgumentException("Cannot read composer.json in directory $this->basePath");
        }
        $json = json_decode(file_get_contents($composerJson), true);
        if (empty($json)) {
            throw new \InvalidArgumentException("invalid composer.json read from $composerJson");
        }
        if (!isset($json['extra']['tars'])) {
            throw new \InvalidArgumentException("extra.tars not defined in $composerJson");
        }

        return new PackageConfig($this->basePath, $json['extra']['tars']);
    }
}

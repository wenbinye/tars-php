<?php

declare(strict_types=1);

namespace wenbinye\tars\deploy;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use wenbinye\tars\server\Composer;

class PackageCommand extends Command
{
    public const COMMAND_NAME = 'package';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composerJson = Composer::detect();
        $basePath = dirname($composerJson);
        $config = $this->loadConfig($composerJson);
        $filesystem = new Filesystem();

        $tempFile = tempnam(sys_get_temp_dir(), 'tars-build');
        @unlink($tempFile);
        $dir = $tempFile.'/'.$config->getServerName();
        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException("Cannot create temporary directory $dir");
        }
        $basePathLen = strlen($basePath);
        $n = 0;
        foreach ($config->getFinders() as $finder) {
            foreach ($finder as $fileInfo) {
                /** @var \SplFileInfo $fileInfo */
                $file = (string) $fileInfo;
                $relPath = substr($file, $basePathLen);
                // error_log("copy $relPath to ${dir}$relPath");
                ++$n;
                if (0 === $n % 100) {
                    $output->writeln("copy $n files to $dir");
                }
                $filesystem->copy($file, $dir.$relPath);
            }
        }
        // 检查 index.php 是否存在
        if (!file_exists($dir.'/src/index.php')) {
            throw new \RuntimeException("the entrance file $basePath/src/index.php does not exist: $dir");
        }

        //打包
        $tgzFile = $basePath.'/'.sprintf('%s_%s.tar.gz', $config->getServerName(), date('YmdHis'));
        $phar = new \PharData($tgzFile);
        $phar->compress(\Phar::GZ);
        $phar->buildFromDirectory($tempFile);
        $filesystem->remove($tempFile);

        $output->writeln("<info>create package $tgzFile</info>");
    }

    private function loadConfig($composerJson): PackageConfig
    {
        $json = Composer::getJson($composerJson);

        return new PackageConfig(dirname($composerJson), $json['extra']['tars']);
    }
}

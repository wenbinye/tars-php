<?php


namespace wenbinye\tars\deploy;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class PackageCommand extends Command
{
    protected function configure()
    {
        $this->setName("package");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $basePath = self::detectProjectPath();
        $config = $this->readConfig($basePath);
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
            foreach ($finder as $file) {
                /** @var \SplFileInfo $file */
                $file = (string) $file;
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

    private function readConfig($basePath): PackageConfig
    {
        $composerJson = $basePath.'/composer.json';
        if (!is_readable($composerJson)) {
            throw new \InvalidArgumentException("Cannot read composer.json in directory $basePath");
        }
        $json = json_decode(file_get_contents($composerJson), true);
        if (empty($json)) {
            throw new \InvalidArgumentException("invalid composer.json read from $composerJson");
        }
        if (!isset($json['extra']['tars'])) {
            throw new \InvalidArgumentException("extra.tars not defined in $composerJson");
        }

        return new PackageConfig($basePath, $json['extra']['tars']);
    }
}
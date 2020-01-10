<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReaderInterface;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

class TarsClientGenerator implements TarsClientGeneratorInterface
{
    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;
    /**
     * @var bool
     */
    private $eval;

    public function __construct(AnnotationReaderInterface $annotationReader, $eval = true)
    {
        $this->annotationReader = $annotationReader;
        $this->eval = $eval;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $clientClassName): string
    {
        $class = new \ReflectionClass($clientClassName);
        if (!$class->isInterface()) {
            throw new \InvalidArgumentException("$clientClassName should be an interface");
        }
        $phpClass = new ClassGenerator($class->getName().'Client'.md5(uniqid('', true)));
        $phpClass->setImplementedInterfaces([$class->getName()]);
        $phpClass->addProperty('client', null, PropertyGenerator::FLAG_PRIVATE);
        $phpClass->addMethod('__construct',
            [
                [
                    'type' => TarsClient::class,
                    'name' => 'client',
                ],
            ],
            MethodGenerator::FLAG_PUBLIC,
            '$this->client = $client;'
        );

        foreach ($class->getMethods() as $reflectionMethod) {
            $phpClass->addMethod(
                $reflectionMethod->getName(),
                array_map(function ($parameter) {
                    return $this->createParameter($parameter);
                }, $reflectionMethod->getParameters()),
                MethodGenerator::FLAG_PUBLIC,
                $this->createBody($reflectionMethod, $this->annotationReader->getMethodAnnotation($reflectionMethod, TarsReturnType::class))
            );
        }
        $this->load($phpClass);

        return $phpClass->getNamespaceName().'\\'.$phpClass->getName();
    }

    private function load(ClassGenerator $phpClass): void
    {
        if (!$this->eval) {
            $fileName = tempnam(sys_get_temp_dir(), 'TarsClientGenerator.php.tmp.');

            file_put_contents($fileName, "<?php\n".$phpClass->generate());
            /* @noinspection PhpIncludeInspection */
            require $fileName;
            unlink($fileName);

            return;
        }
        eval($phpClass->generate());
    }

    private function createParameter(\ReflectionParameter $parameter): array
    {
        return array_filter([
            'name' => $parameter->getName(),
            'type' => (string) $parameter->getType(),
            'PassedByReference' => $parameter->isPassedByReference(),
        ]);
    }

    private function createBody(\ReflectionMethod $reflectionMethod, $returnType): string
    {
        $parameters = [];
        $outParameters = [];
        $hasReturnValue = ($returnType && 'void' !== $returnType->type);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            if ($parameter->isPassedByReference()) {
                $outParameters[] = $parameter->name;
            } else {
                $parameters[] = $parameter->name;
            }
        }
        if ($hasReturnValue) {
            $returnValueName = 'ret';
            $i = 1;
            while (in_array($returnValueName, $outParameters, true)) {
                $returnValueName = 'ret'.$i++;
            }
            $outParameters[] = $returnValueName;
        }
        $call = '$this->client->send($this, __FUNCTION__'.
            (empty($parameters) ? '' : ', '.$this->buildParameters($parameters)).');';
        if (empty($outParameters)) {
            return $call;
        }
        $body = 'list ('.$this->buildParameters($outParameters).') = '.$call;
        if ($hasReturnValue) {
            $body .= "\nreturn $".end($outParameters).';';
        }

        return $body;
    }

    private function buildParameters(array $parameters): string
    {
        return implode(', ', array_map(static function ($name) {
            return '$'.$name;
        }, $parameters));
    }
}

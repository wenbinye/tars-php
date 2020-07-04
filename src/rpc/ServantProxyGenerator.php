<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReaderInterface;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Reflection\DocBlockReflection;
use wenbinye\tars\protocol\annotation\TarsReturnType;

class ServantProxyGenerator implements ServantProxyGeneratorInterface
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
    public function generate(string $clientClassName, ?string $servant = null): string
    {
        $phpClass = $this->createClassGenerator($clientClassName, $servant);
        $this->load($phpClass);

        return $phpClass->getNamespaceName().'\\'.$phpClass->getName();
    }

    private function load(ClassGenerator $phpClass): void
    {
        $code = $phpClass->generate();
        if ($this->eval) {
            eval($code);
        } else {
            $fileName = tempnam(sys_get_temp_dir(), 'TarsClientGenerator.php.tmp.');

            file_put_contents($fileName, "<?php\n".$code);
            /* @noinspection PhpIncludeInspection */
            require $fileName;
            unlink($fileName);

            return;
        }
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
        $call = '$this->client->call($this, __FUNCTION__'.
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

    /**
     * @throws \ReflectionException
     */
    public function createClassGenerator(string $clientClassName, ?string $servantName): ClassGenerator
    {
        $class = new \ReflectionClass($clientClassName);
        if (!$class->isInterface()) {
            throw new \InvalidArgumentException("$clientClassName should be an interface");
        }
        $phpClass = new ClassGenerator(
            $class->getShortName().'Client'.md5(uniqid('', true)),
            $class->getNamespaceName(),
            $flags = null,
            $extends = null,
            $interfaces = [],
            $properties = [],
            $methods = [],
            DocBlockGenerator::fromReflection(new DocBlockReflection($this->createDocBlock($class->getDocComment(), $servantName)))
        );

        $phpClass->setImplementedInterfaces([$class->getName()]);
        $phpClass->addProperty('client', null, PropertyGenerator::FLAG_PRIVATE);
        $phpClass->addMethod('__construct',
            [
                [
                    'type' => TarsClientInterface::class,
                    'name' => 'client',
                ],
            ],
            MethodGenerator::FLAG_PUBLIC,
            '$this->client = $client;'
        );

        foreach ($class->getMethods() as $reflectionMethod) {
            $methodBody = $this->createBody($reflectionMethod, $this->annotationReader->getMethodAnnotation($reflectionMethod, TarsReturnType::class));
            $phpClass->addMethod(
                $reflectionMethod->getName(),
                array_map(function ($parameter) {
                    return $this->createParameter($parameter);
                }, $reflectionMethod->getParameters()),
                MethodGenerator::FLAG_PUBLIC,
                $methodBody
            );
        }

        return $phpClass;
    }

    private function createDocBlock(string $docComment, ?string $servantName): string
    {
        if ($servantName) {
            return "/**\n"
                .sprintf(" * @\\%s(name=\"%s\")\n", \wenbinye\tars\protocol\annotation\TarsClient::class, $servantName)
                .'*/';
        }

        return $docComment;
    }
}

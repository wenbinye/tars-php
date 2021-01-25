<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReaderInterface;
use kuiper\helper\Text;
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

    public function __construct(AnnotationReaderInterface $annotationReader, bool $eval = true)
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
        // echo $code;
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
        $parameterType = $parameter->getType();

        return array_filter([
            'name' => $parameter->getName(),
            'type' => isset($parameterType) ? ($parameter->allowsNull() ? '?' : '').$parameterType : null,
            'PassedByReference' => $parameter->isPassedByReference(),
        ]);
    }

    private function createBody(\ReflectionMethod $reflectionMethod, ?TarsReturnType $returnType): string
    {
        $parameters = [];
        $outParameters = [];
        $hasReturnValue = (null !== $returnType && 'void' !== $returnType->type);

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
        return implode(', ', array_map(static function ($name): string {
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
        $constructDocBlock = null;
        if (null !== $servantName) {
            $constructDocBlock = DocBlockGenerator::fromReflection(
                new DocBlockReflection($this->createDocBlock($servantName)));
        }
        $phpClass = new ClassGenerator(
            $class->getShortName().'Client'.md5(uniqid('', true)),
            Text::isNotEmpty($class->getNamespaceName()) ? $class->getNamespaceName() : null,
            $flags = null,
            $extends = null,
            $interfaces = [],
            $properties = [],
            $methods = [],
            $constructDocBlock
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
            /** @var TarsReturnType|null $returnType */
            $returnType = $this->annotationReader->getMethodAnnotation($reflectionMethod, TarsReturnType::class);
            $methodBody = $this->createBody($reflectionMethod, $returnType);
            $methodGenerator = new MethodGenerator(
                $reflectionMethod->getName(),
                array_map(function ($parameter): array {
                    return $this->createParameter($parameter);
                }, $reflectionMethod->getParameters()),
                MethodGenerator::FLAG_PUBLIC,
                $methodBody,
                DocBlockGenerator::fromReflection(new DocBlockReflection('/** @inheritdoc */'))
            );
            $methodGenerator->setReturnType($reflectionMethod->getReturnType());
            $phpClass->addMethodFromGenerator($methodGenerator);
        }

        return $phpClass;
    }

    private function createDocBlock(string $servantName): string
    {
        return "/**\n"
            .sprintf(" * @\\%s(name=\"%s\")\n", \wenbinye\tars\protocol\annotation\TarsClient::class, $servantName)
            .'*/';
    }
}

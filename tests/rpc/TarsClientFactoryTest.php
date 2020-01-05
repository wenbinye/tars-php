<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Monolog\Test\TestCase;
use ProxyManager\Generator\ClassGenerator;
use wenbinye\tars\log\LogServant;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\rpc\fixtures\TestTafServiceServant;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\PropertyGenerator;

class TarsClientFactoryTest extends TestCase
{
    public function testName()
    {
        AnnotationRegistry::registerLoader('class_exists');
        $annotationReader = new AnnotationReader();
        // $class = new \ReflectionClass(LogServant::class);
        $class = new \ReflectionClass(TestTafServiceServant::class);
        if (!$class->isInterface()) {
            throw new \InvalidArgumentException('');
        }
        $phpClass = new ClassGenerator($class->getName().'Client');
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
                $this->createBody($reflectionMethod, $annotationReader->getMethodAnnotation($reflectionMethod, TarsReturnType::class))
            );
        }
        echo $phpClass->generate();
    }

    private function createParameter(\ReflectionParameter $parameter): array
    {
        return array_filter([
            'name' => $parameter->getName(),
            'type' => (string) $parameter->getType(),
            'PassedByReference' => $parameter->isPassedByReference(),
        ]);
    }

    private function createBody(\ReflectionMethod $reflectionMethod, ?TarsReturnType $returnType)
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

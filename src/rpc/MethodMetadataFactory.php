<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use kuiper\annotations\AnnotationReaderInterface;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\rpc\exception\InvalidClientException;

/**
 * 读取调用方法 rpc ServantName, 参数，返回值等信息.
 *
 * Class MethodMetadataFactory
 */
class MethodMetadataFactory implements MethodMetadataFactoryInterface
{
    /**
     * @var AnnotationReaderInterface
     */
    private $annotationReader;

    /**
     * @var MethodMetadata[]
     */
    private $cache;

    public function __construct(AnnotationReaderInterface $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * {@inheritdoc}
     */
    public function create($servant, string $method): MethodMetadata
    {
        $key = get_class($servant).'::'.$method;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        try {
            return $this->cache[$key] = $this->getMetadataFromAnnotation($servant, $method);
        } catch (\ReflectionException $e) {
            throw new InvalidClientException('read method metadata failed', $e);
        }
    }

    /**
     * @param object $servant
     *
     * @throws \ReflectionException
     */
    private function getMetadataFromAnnotation($servant, string $method): MethodMetadata
    {
        $reflectionClass = new \ReflectionClass($servant);
        foreach ($reflectionClass->getInterfaces() as $interface) {
            if (!$interface->hasMethod($method)) {
                continue;
            }
            /** @var TarsServant $servantAnnotation */
            $servantAnnotation = $this->annotationReader->getClassAnnotation($interface, TarsServant::class);
            if (!$servantAnnotation) {
                continue;
            }
            $reflectionMethod = $interface->getMethod($method);
            $parameters = [];
            $outputParameters = [];
            $returnType = null;
            foreach ($this->annotationReader->getMethodAnnotations($reflectionMethod) as $methodAnnotation) {
                if ($methodAnnotation instanceof TarsParameter) {
                    if ($methodAnnotation->out) {
                        $outputParameters[] = $methodAnnotation;
                    } else {
                        $parameters[] = $methodAnnotation;
                    }
                } elseif ($methodAnnotation instanceof TarsReturnType) {
                    $returnType = $methodAnnotation;
                }
            }

            return new MethodMetadata($interface->getName(), $interface->getNamespaceName(), $method,
                $servantAnnotation->name, $parameters, $outputParameters, $returnType);
        }
        throw new InvalidClientException(sprintf("%s does not contain valid method definition, check it's interfaces should annotated with @TarsServant", get_class($servant)));
    }
}

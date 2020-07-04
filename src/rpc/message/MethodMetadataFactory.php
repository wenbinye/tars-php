<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc\message;

use kuiper\annotations\AnnotationReaderInterface;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnType;
use wenbinye\tars\protocol\annotation\TarsServant;
use wenbinye\tars\rpc\exception\InvalidMethodException;

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
            throw new InvalidMethodException('read method metadata failed', $e);
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
        if (!$reflectionClass->hasMethod($method)) {
            throw new InvalidMethodException(sprintf("%s does not contain method '$method'", $reflectionClass));
        }
        $servantAnnotation = $this->getTarsServantAnnotation($reflectionClass);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $parameters = [];
        $returnType = null;
        foreach ($this->getMethodAnnotations($reflectionMethod) as $methodAnnotation) {
            if ($methodAnnotation instanceof TarsParameter) {
                $parameters[] = $methodAnnotation;
            } elseif ($methodAnnotation instanceof TarsReturnType) {
                $returnType = $methodAnnotation;
            }
        }

        return new MethodMetadata(
            $reflectionClass->getName(),
            $reflectionClass->getNamespaceName(),
            $method,
            $servantAnnotation->name,
            $parameters,
            $returnType);
    }

    private function getTarsServantAnnotation(\ReflectionClass $reflectionClass): TarsServant
    {
        $annotation = $this->annotationReader->getClassAnnotation($reflectionClass, TarsServant::class);
        if ($annotation) {
            return $annotation;
        }
        if (false !== ($parent = $reflectionClass->getParentClass())) {
            return $this->getTarsServantAnnotation($parent);
        }
        foreach ($reflectionClass->getInterfaces() as $interface) {
            $annotation = $this->annotationReader->getClassAnnotation($interface, TarsServant::class);
            if ($annotation) {
                return $annotation;
            }
        }

        throw new InvalidMethodException(sprintf('%s does not contain valid method definition, '."check it's interfaces should annotated with @TarsServant", $reflectionClass));
    }

    private function getMethodAnnotations(\ReflectionMethod $method): array
    {
        $docComment = $method->getDocComment();
        if ($docComment && false !== stripos($docComment, '@inheritdoc')) {
            $name = $method->getName();
            $class = $method->getDeclaringClass();
            if (false !== ($parent = $class->getParentClass())) {
                if ($parent->hasMethod($name)) {
                    return $this->getMethodAnnotations($parent->getMethod($name));
                }
            }
            foreach ($class->getInterfaces() as $interface) {
                if ($interface->hasMethod($name)) {
                    return $this->annotationReader->getMethodAnnotations($interface->getMethod($name));
                }
            }
        }

        return $this->annotationReader->getMethodAnnotations($method);
    }
}

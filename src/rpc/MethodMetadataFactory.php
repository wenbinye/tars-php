<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use Doctrine\Common\Annotations\Reader;
use wenbinye\tars\protocol\annotation\TarsClient;
use wenbinye\tars\protocol\annotation\TarsParameter;
use wenbinye\tars\protocol\annotation\TarsReturnValue;
use wenbinye\tars\rpc\exception\InvalidClientException;

/**
 * 读取调用方法 rpc ServantName, 参数，返回值等信息.
 *
 * Class MethodMetadataFactory
 */
class MethodMetadataFactory implements MethodMetadataFactoryInterface
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var MethodMetadata[]
     */
    private $cache;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * {@inheritdoc}
     */
    public function create($client, string $method): MethodMetadata
    {
        $key = get_class($client).'::'.$method;
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        try {
            return $this->cache[$key] = $this->getMetadataFromAnnotation($client, $method);
        } catch (\ReflectionException $e) {
            throw new InvalidClientException('read method metadata failed', $e);
        }
    }

    /**
     * @param object $client
     *
     * @throws \ReflectionException
     */
    private function getMetadataFromAnnotation($client, string $method): MethodMetadata
    {
        $reflectionClass = new \ReflectionClass($client);
        foreach ($reflectionClass->getInterfaces() as $interface) {
            if (!$interface->hasMethod($method)) {
                continue;
            }
            /** @var TarsClient $clientAnnotation */
            $clientAnnotation = $this->annotationReader->getClassAnnotation($interface, TarsClient::class);
            if (!$clientAnnotation) {
                continue;
            }
            $reflectionMethod = $interface->getMethod($method);
            $parameters = [];
            $returnValues = [];
            foreach ($this->annotationReader->getMethodAnnotations($reflectionMethod) as $methodAnnotation) {
                if ($methodAnnotation instanceof TarsParameter) {
                    if ($methodAnnotation->out) {
                        $returnValue = new TarsReturnValue();
                        $returnValue->type = $methodAnnotation->type;
                        $returnValue->name = $methodAnnotation->name;
                        $returnValues[] = $returnValue;
                    } else {
                        $parameters[] = $methodAnnotation;
                    }
                } elseif ($methodAnnotation instanceof TarsReturnValue) {
                    $returnValues[] = $methodAnnotation;
                }
            }

            return new MethodMetadata($interface->getName(), $interface->getNamespaceName(), $method,
                $clientAnnotation->servant, $parameters, $returnValues);
        }
        throw new InvalidClientException(sprintf("%s does not contain valid method definition, check it's interfaces should annotated with @TarsClient", get_class($client)));
    }
}

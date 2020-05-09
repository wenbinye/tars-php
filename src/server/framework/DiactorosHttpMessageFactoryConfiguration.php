<?php

declare(strict_types=1);

namespace wenbinye\tars\server\framework;

use kuiper\di\annotation\Bean;
use kuiper\di\annotation\ConditionalOnClass;
use kuiper\di\annotation\Configuration;
use kuiper\swoole\http\DiactorosSwooleRequestBridge;
use kuiper\swoole\http\SwooleRequestBridgeInterface;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Diactoros\UriFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

/**
 * @Configuration()
 * @ConditionalOnClass(ServerRequestFactory::class)
 */
class DiactorosHttpMessageFactoryConfiguration
{
    /**
     * @Bean()
     */
    public function serverRequestFactory(): ServerRequestFactoryInterface
    {
        return new ServerRequestFactory();
    }

    /**
     * @Bean()
     */
    public function responseFactory(): ResponseFactoryInterface
    {
        return new ResponseFactory();
    }

    /**
     * @Bean()
     */
    public function streamFactory(): StreamFactoryInterface
    {
        return new StreamFactory();
    }

    /**
     * @Bean()
     */
    public function uriFactory(): UriFactoryInterface
    {
        return new UriFactory();
    }

    /**
     * @Bean()
     */
    public function uploadFileFactory(): UploadedFileFactoryInterface
    {
        return new UploadedFileFactory();
    }

    /**
     * @Bean()
     */
    public function swooleRequestBridge(): SwooleRequestBridgeInterface
    {
        return new DiactorosSwooleRequestBridge();
    }
}

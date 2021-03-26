<?php

declare(strict_types=1);

namespace wenbinye\tars\stat;

use kuiper\annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;
use wenbinye\tars\protocol\Packer;
use wenbinye\tars\rpc\fixtures\HelloServiceClient;
use wenbinye\tars\rpc\message\ClientRequestFactory;
use wenbinye\tars\rpc\message\MethodMetadataFactory;
use wenbinye\tars\rpc\message\RequestIdGenerator;
use wenbinye\tars\rpc\message\ServerResponse;
use wenbinye\tars\server\ServerProperties;

class SwooleTableStatStoreTest extends TestCase
{
    public function testGetEntries()
    {
        $reader = AnnotationReader::getInstance();
        $packer = new Packer($reader);
        $client = new HelloServiceClient();
        $methodMetadataFactory = new MethodMetadataFactory($reader);

        $factory = new ClientRequestFactory($methodMetadataFactory, $packer, new RequestIdGenerator());
        $reportInterval = 60000;
        $time = time();
        $timeSlice = (int) ($time / ($reportInterval / 1000));

        $response = new ServerResponse($factory->createRequest($client, 'hello', ['']), []);
        $serverProperties = new ServerProperties();
        $stat = StatEntry::success($timeSlice, $serverProperties, $response, 124);

        $store = new SwooleTableStatStore();
        $store->save($stat);
        $entries = iterator_to_array($store->getEntries($timeSlice + 1));
        // var_export($entries);
        $this->assertCount(1, $entries);
        array_map([$store, 'delete'], $entries);
        $entries = iterator_to_array($store->getEntries($timeSlice + 1));
        // var_export($entries);
        $this->assertEmpty($entries);
    }
}

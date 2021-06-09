<?php

declare(strict_types=1);

namespace wenbinye\tars\functional;

use wenbinye\tars\client\LogServant;
use wenbinye\tars\rpc\message\ClientRequestFactoryInterface;
use wenbinye\tars\rpc\message\RequestAttribute;
use wenbinye\tars\rpc\message\ResponseFactoryInterface;
use wenbinye\tars\rpc\message\ServerResponse;
use wenbinye\tars\rpc\route\RouteResolverInterface;
use wenbinye\tars\stat\StatInterface;

class StatTest extends FunctionalTestCase
{
    public function testSendStat()
    {
        $container = $this->getContainer();
        /** @var RouteResolverInterface $routeResolver */
        $routeResolver = $container->get(RouteResolverInterface::class);
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        $log = $container->get(LogServant::class);
        $servantName = 'tars.tarslog.LogObj';

        $request = $container->get(ClientRequestFactoryInterface::class)->createRequest($log, 'logger', []);
        $stat = $container->get(StatInterface::class);

        foreach (range(300, 600) as $time) {
            $serverRequests = $request->withAttribute(RequestAttribute::SERVER_ADDR,
                $routeResolver->resolve($servantName)->getAddressList()[0]->getAddress())
                ->withAttribute(RequestAttribute::TIME, time() - $time);
            $response = new ServerResponse($serverRequests, [], 0);
            $response = $responseFactory->create($response->getBody(), $serverRequests);

            $stat->success($response, 30);
            $stat->fail($response, 3);
            $stat->timedOut($response, 3000);
        }
        $stat->send();
        $this->assertTrue(true);
    }
}

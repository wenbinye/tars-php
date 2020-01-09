<?php


namespace wenbinye\tars\server;


use Psr\Container\ContainerInterface;

interface ContainerFactoryInterface
{
    public function create(): ContainerInterface;
}
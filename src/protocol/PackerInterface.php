<?php


namespace wenbinye\tars\protocol;


interface PackerInterface
{
    public function pack($data, $type): string;

    public function unpack(string $payload, $type);
}
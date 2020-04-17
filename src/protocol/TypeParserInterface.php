<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\exception\SyntaxErrorException;
use wenbinye\tars\protocol\type\Type;

interface TypeParserInterface
{
    /**
     * @throws SyntaxErrorException
     */
    public function parse(string $type, string $namespace = ''): Type;
}

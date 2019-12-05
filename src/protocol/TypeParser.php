<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\exception\SyntaxErrorException;
use wenbinye\tars\protocol\type\MapType;
use wenbinye\tars\protocol\type\PrimitiveType;
use wenbinye\tars\protocol\type\StructType;
use wenbinye\tars\protocol\type\Type;
use wenbinye\tars\protocol\type\VectorType;
use wenbinye\tars\protocol\type\VoidType;

/**
 * tars_type: vector< vector_sub_type > :
 *            map< key_type, value_type > :
 *            primitive_type :
 *            custom_type.
 *
 * Class TypeParser
 */
class TypeParser
{
    /**
     * @throws SyntaxErrorException
     */
    public function parse(string $type, string $namespace): Type
    {
        $tokens = (new TypeTokenizer($type))->tokenize();

        return $this->createType($tokens, $namespace);
    }

    /**
     * @throws SyntaxErrorException
     */
    private function createType(array &$tokens, string $namespace): Type
    {
        if (empty($tokens)) {
            throw new SyntaxErrorException('expect one type');
        }
        $token = array_shift($tokens);
        if (TypeTokenizer::T_PRIMITIVE === $token[0]) {
            return new PrimitiveType($token[1]);
        }

        if (TypeTokenizer::T_STRUCT === $token[0]) {
            return new StructType($namespace.'\\'.$token[1]);
        }

        if (TypeTokenizer::T_VOID === $token[0]) {
            return new VoidType();
        }

        if (TypeTokenizer::T_VECTOR === $token[0]) {
            $this->match(array_shift($tokens), TypeTokenizer::T_LEFT_BRACKET);
            $subType = $this->createType($tokens, $namespace);
            $this->match(array_shift($tokens), TypeTokenizer::T_RIGHT_BRACKET);

            return new VectorType($subType);
        }

        if (TypeTokenizer::T_MAP === $token[0]) {
            $this->match(array_shift($tokens), TypeTokenizer::T_LEFT_BRACKET);
            $keyType = $this->createType($tokens, $namespace);
            $this->match(array_shift($tokens), TypeTokenizer::T_COMMA);
            $valueType = $this->createType($tokens, $namespace);
            $this->match(array_shift($tokens), TypeTokenizer::T_RIGHT_BRACKET);

            return new MapType($keyType, $valueType);
        }
        throw new SyntaxErrorException('unknown type');
    }

    /**
     * @throws SyntaxErrorException
     */
    private function match(array $token, int $tokenType): void
    {
        if ($token[0] !== $tokenType) {
            throw new SyntaxErrorException("token not match $tokenType");
        }
    }
}

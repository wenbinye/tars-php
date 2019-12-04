<?php

namespace wenbinye\tars\protocol;

use Cassandra\Map;

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

    private function createType(array &$tokens, string $namespace): Type
    {
        if (empty($tokens)) {
            throw new SyntaxErrorException('expect one type');
        }
        $token = array_shift($tokens);
        if (TypeTokenizer::T_PRIMITIVE === $token[0]) {
            return new PrimitiveType($token[1]);
        } elseif (TypeTokenizer::T_STRUCT === $token[0]) {
        } elseif (TypeTokenizer::T_VOID === $token[0]) {
            return new VoidType();
        } elseif (TypeTokenizer::T_VECTOR === $token[0]) {
            $this->match(array_shift($tokens), TypeTokenizer::T_LEFT_BRAKET);
            $subType = $this->createType($tokens, $namespace);
            $this->match(array_shift($tokens), TypeTokenizer::T_RIGHT_BRAKET);

            return new VectorType($subType);
        } elseif (TypeTokenizer::T_MAP == $token[0]) {
            $this->match(array_shift($tokens), TypeTokenizer::T_LEFT_BRAKET);
            $keyType = $this->createType($tokens, $namespace);
            $this->match(array_shift($tokens), TypeTokenizer::T_COMMA);
            $valueType = $this->createType($tokens, $namespace);
            $this->match(array_shift($tokens), TypeTokenizer::T_RIGHT_BRAKET);

            return new MapType($keyType, $valueType);
        }
    }
}

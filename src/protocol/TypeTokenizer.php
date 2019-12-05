<?php

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\exception\SyntaxErrorException;

class TypeTokenizer
{
    const LEFT_BRACKET = '<';
    const RIGHT_BRACKET = '>';
    const COMMA = ',';
    const VECTOR = 'vector';
    const MAP = 'map';
    const VOID = 'void';
    const UNSIGNED = 'unsigned';

    const T_VOID = 0;
    const T_VECTOR = 1;
    const T_MAP = 2;
    const T_PRIMITIVE = 3;
    const T_STRUCT = 4;

    const T_LEFT_BRACKET = 10;
    const T_RIGHT_BRACKET = 11;
    const T_COMMA = 12;

    private static $STOP_CHARS = [
        self::LEFT_BRACKET => self::T_LEFT_BRACKET,
        self::RIGHT_BRACKET => self::T_RIGHT_BRACKET,
        self::COMMA => self::T_COMMA,
    ];

    private static $RESERVE_WORDS = [
        self::VOID => self::T_VOID,
        self::VECTOR => self::T_VECTOR,
        self::MAP => self::T_MAP,
    ];

    private static $PRIMITIVES = [
        'bool' => \TARS::BOOL,
        'boolean' => \TARS::BOOL,
        'byte' => \TARS::CHAR,
        'char' => \TARS::CHAR,
        'unsigned byte' => \TARS::UINT8,
        'unsigned char' => \TARS::UINT8,
        'short' => \TARS::SHORT,
        'unsigned short' => \TARS::UINT16,
        'int' => \TARS::INT32,
        'unsigned int' => \TARS::UINT32,
        'long' => \TARS::INT64,
        'float' => \TARS::FLOAT,
        'double' => \TARS::DOUBLE,
        'string' => \TARS::STRING,
    ];

    /**
     * @var string
     */
    private $input;
    /**
     * @var int
     */
    private $pos;
    /**
     * @var int
     */
    private $length;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->length = strlen($input);
        $this->pos = 0;
    }

    private function nextChar()
    {
        if ($this->pos >= $this->length) {
            throw new \OutOfBoundsException('no more char');
        }
        $char = $this->input[$this->pos];
        ++$this->pos;

        return $char;
    }

    private function putBack(): void
    {
        --$this->pos;
    }

    private function createToken(int $tokenType, $tokenValue = null): array
    {
        return [$tokenType, $tokenValue];
    }

    /**
     * @throws SyntaxErrorException
     */
    public function tokenize(): array
    {
        $tokens = [];
        while ($token = $this->nextToken()) {
            $tokens[] = $token;
        }

        return $tokens;
    }

    private function nextToken()
    {
        $this->skipWhitespace();
        if ($this->isEnd()) {
            return null;
        }
        $char = $this->nextChar();
        if (isset(self::$STOP_CHARS[$char])) {
            return $this->createToken(self::$STOP_CHARS[$char]);
        }

        $this->putBack();
        $word = $this->readIdentifier();
        if (isset(self::$RESERVE_WORDS[$word])) {
            return $this->createToken(self::$RESERVE_WORDS[$word]);
        }

        if (self::UNSIGNED === $word) {
            $this->skipWhitespace();
            $unsignedType = $word.' '.$this->readIdentifier();
            if (!isset(self::$PRIMITIVES[$unsignedType])) {
                $this->raiseSyntaxError('expect byte|short|int for unsigned type');
            }

            return $this->createToken(self::T_PRIMITIVE, self::$PRIMITIVES[$unsignedType]);
        }

        if (isset(self::$PRIMITIVES[$word])) {
            return $this->createToken(self::T_PRIMITIVE, self::$PRIMITIVES[$word]);
        }

        return $this->createToken(self::T_STRUCT, $word);
    }

    private function isWhitespace($char): bool
    {
        return in_array($char, [' ', "\t", "\n"], true);
    }

    private function isEnd(): bool
    {
        return $this->pos >= $this->length;
    }

    private function skipWhitespace(): void
    {
        while (!$this->isEnd()) {
            $char = $this->nextChar();
            if (!$this->isWhitespace($char)) {
                $this->putBack();
                break;
            }
        }
    }

    /**
     * @throws SyntaxErrorException
     */
    private function readIdentifier(): string
    {
        $word = '';
        while (!$this->isEnd()) {
            $char = $this->nextChar();
            if (!$this->isIdentifier($char)) {
                $this->putBack();
                break;
            }
            $word .= $char;
        }
        if (empty($word)) {
            $this->raiseSyntaxError('expected identifier');
        }

        return $word;
    }

    private function isIdentifier($char): bool
    {
        return preg_match('/\w/', $char);
    }

    private function raiseSyntaxError(string $message): void
    {
        throw new SyntaxErrorException($message.' at '.$this->pos.', type='.$this->input);
    }
}

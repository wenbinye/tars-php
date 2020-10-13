<?php

declare(strict_types=1);

namespace wenbinye\tars\protocol;

use wenbinye\tars\protocol\exception\SyntaxErrorException;

class TypeTokenizer
{
    public const LEFT_BRACKET = '<';
    public const RIGHT_BRACKET = '>';
    public const COMMA = ',';
    public const VECTOR = 'vector';
    public const MAP = 'map';
    public const VOID = 'void';
    public const UNSIGNED = 'unsigned';

    public const T_VOID = 0;
    public const T_VECTOR = 1;
    public const T_MAP = 2;
    public const T_PRIMITIVE = 3;
    public const T_STRUCT = 4;

    public const T_LEFT_BRACKET = 10;
    public const T_RIGHT_BRACKET = 11;
    public const T_COMMA = 12;

    /**
     * @var int[]
     */
    private static $STOP_CHARS = [
        self::LEFT_BRACKET => self::T_LEFT_BRACKET,
        self::RIGHT_BRACKET => self::T_RIGHT_BRACKET,
        self::COMMA => self::T_COMMA,
    ];

    /**
     * @var int[]
     */
    private static $RESERVE_WORDS = [
        self::VOID => self::T_VOID,
        self::VECTOR => self::T_VECTOR,
        self::MAP => self::T_MAP,
    ];

    /**
     * @var array
     */
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

    private function nextChar(): string
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

    /**
     * @param int             $tokenType
     * @param int|string|null $tokenValue
     *
     * @return array
     */
    private function createToken(int $tokenType, $tokenValue = null): array
    {
        return [$tokenType, $tokenValue];
    }

    public function tokenize(): array
    {
        $tokens = [];
        while ($token = $this->nextToken()) {
            $tokens[] = $token;
        }

        return $tokens;
    }

    private function nextToken(): ?array
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

    private function isWhitespace(string $char): bool
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

    private function isIdentifier(string $char): bool
    {
        return (bool) preg_match('/\w/', $char);
    }

    private function raiseSyntaxError(string $message): void
    {
        throw new SyntaxErrorException($message.' at '.$this->pos.', type='.$this->input);
    }
}

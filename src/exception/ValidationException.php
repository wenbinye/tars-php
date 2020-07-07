<?php

declare(strict_types=1);

namespace wenbinye\tars\exception;

use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Our custom exception which mean that validation fail.
 *
 * The main purpose on this exception
 * is to store validation errors
 * and provide simple way to obtain this errors further.
 */
class ValidationException extends \Exception
{
    /**
     * This array contains validation errors (violations).
     *
     * @var ConstraintViolationListInterface Validation errors (violations). By default it's empty array.
     */
    private $violations;

    /**
     * Constructor.
     *
     * @param ConstraintViolationListInterface $violations array with validation errors (violations)
     */
    public function __construct($violations, int $code = 0)
    {
        $this->violations = $violations;
        parent::__construct($this->getFullMessage(), $code);
    }

    /**
     * Provides array with all validation errors (violations).
     *
     * IMPORTANT error message structure:
     * Each invalid parameter have array with error messages.
     *
     * @return array array with errors messages
     */
    public function getMessages(): array
    {
        $messages = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($this->violations as $violation) {
            $name = $violation->getPropertyPath();
            $messages[$name][] = "[$name]".$violation->getMessage();
        }

        return $messages;
    }

    /**
     * Provides array with all validation errors (violations).
     *
     * IMPORTANT error message structure:
     * Each invalid parameter have string with joined all error messages into single message.
     *
     * @return string the error message
     */
    public function getFullMessage($delimiter = ': '): string
    {
        return implode($delimiter, array_merge(...array_values($this->getMessages())));
    }
}

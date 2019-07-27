<?php


namespace PhpLogger\Format;


class Loggable
{
    private $message;
    private $context;

    public function __construct(string $message, array $context)
    {
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * @return string Properly formatted message
     */
    public function toString(): string
    {
        return $this->message;
    }

    /**
     * @return array Properly formatted context
     */
    public function toArray(): array
    {
        $this->context;
    }
}
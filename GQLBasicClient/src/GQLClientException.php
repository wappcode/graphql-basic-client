<?php

namespace GQLBasicClient;

/**
 * Exception thrown when GraphQL client encounters an error
 */
class GQLClientException extends \Exception
{
    /**
     * @var array|null
     */
    private $context;

    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     * @param array|null $context
     */
    public function __construct(
        string $message = "",
        int $code = 0,
        \Throwable $previous = null,
        ?array $context = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context about the error
     *
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }
}

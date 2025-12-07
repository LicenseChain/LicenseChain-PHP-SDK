<?php

namespace LicenseChain\Exceptions;

/**
 * Exception thrown when rate limit is exceeded
 */
class RateLimitException extends LicenseChainException
{
    protected ?int $retryAfter = null;
    protected ?int $limit = null;
    protected ?int $remaining = null;
    protected ?int $reset = null;

    public function __construct(
        string $message = 'Rate limit exceeded',
        ?string $code = null,
        ?int $statusCode = null,
        array $details = [],
        ?int $retryAfter = null,
        ?int $limit = null,
        ?int $remaining = null,
        ?int $reset = null
    ) {
        parent::__construct($message, $code, $statusCode, $details);
        $this->retryAfter = $retryAfter;
        $this->limit = $limit;
        $this->remaining = $remaining;
        $this->reset = $reset;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getRemaining(): ?int
    {
        return $this->remaining;
    }

    public function getReset(): ?int
    {
        return $this->reset;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'retry_after' => $this->retryAfter,
            'limit' => $this->limit,
            'remaining' => $this->remaining,
            'reset' => $this->reset,
        ]);
    }
}

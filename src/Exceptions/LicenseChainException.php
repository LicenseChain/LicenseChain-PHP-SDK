<?php

namespace LicenseChain\Exceptions;

/**
 * Base exception for all LicenseChain errors
 */
class LicenseChainException extends \Exception
{
    protected ?string $code = null;
    protected ?int $statusCode = null;
    protected array $details = [];

    public function __construct(
        string $message = 'LicenseChain error occurred',
        ?string $code = null,
        ?int $statusCode = null,
        array $details = []
    ) {
        parent::__construct($message);
        $this->code = $code;
        $this->statusCode = $statusCode;
        $this->details = $details;
    }

    public function getErrorCode(): ?string
    {
        return $this->code;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->code,
            'status_code' => $this->statusCode,
            'details' => $this->details,
        ];
    }
}

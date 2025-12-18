<?php

namespace LicenseChain\Exceptions;

/**
 * Exception thrown when request validation fails
 */
class ValidationException extends LicenseChainException
{
    public function __construct(
        string $message = 'Validation failed',
        ?string $code = null,
        ?int $statusCode = null,
        array $details = []
    ) {
        parent::__construct($message, $code, $statusCode, $details);
    }
}

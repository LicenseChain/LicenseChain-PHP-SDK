<?php

namespace LicenseChain\Exceptions;

/**
 * Exception thrown when network operations fail
 */
class NetworkException extends LicenseChainException
{
    public function __construct(
        string $message = 'Network error',
        ?string $code = null,
        ?int $statusCode = null,
        array $details = []
    ) {
        parent::__construct($message, $code, $statusCode, $details);
    }
}

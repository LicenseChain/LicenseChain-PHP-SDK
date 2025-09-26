<?php

namespace LicenseChain\Exceptions;

/**
 * Exception thrown when authentication fails
 */
class AuthenticationException extends LicenseChainException
{
    public function __construct(
        string $message = 'Authentication failed',
        ?string $code = null,
        ?int $statusCode = null,
        array $details = []
    ) {
        parent::__construct($message, $code, $statusCode, $details);
    }
}

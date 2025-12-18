<?php

namespace LicenseChain\Exceptions;

/**
 * Exception thrown when server returns an error
 */
class ServerException extends LicenseChainException
{
    public function __construct(
        string $message = 'Server error',
        ?string $code = null,
        ?int $statusCode = null,
        array $details = []
    ) {
        parent::__construct($message, $code, $statusCode, $details);
    }
}

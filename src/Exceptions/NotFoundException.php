<?php

namespace LicenseChain\Exceptions;

/**
 * Exception thrown when a resource is not found
 */
class NotFoundException extends LicenseChainException
{
    public function __construct(
        string $message = 'Resource not found',
        ?string $code = null,
        ?int $statusCode = null,
        array $details = []
    ) {
        parent::__construct($message, $code, $statusCode, $details);
    }
}

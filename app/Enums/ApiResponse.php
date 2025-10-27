<?php

namespace App\Enums;

enum ApiResponse:string
{
    case SUCCESS = 'success';
    case NOT_FOUND = 'not_found';
    case VALIDATION_ERROR = 'validation_error';
    case ALREADY_EXISTS = 'already_exists';
    case DATABASE_ERROR = 'database_error';
    case UNAUTHORIZED = 'unauthorized';
    case UNAUTHENTICATED = 'unauthenticated';
    case FORBIDDEN = 'forbidden';
    case EXPIRED = 'expired';
    case INVALID_TOKEN = 'invalid_token';
    case AUTH_FAILURE = 'auth_failure';
    case ERROR = 'error';
    
    /**
     * Get a human-readable message for the response
     */
    public function getMessage(): string
    {
        return match($this) {
            self::SUCCESS => 'Operation completed successfully',
            self::NOT_FOUND => 'Requested resource was not found',
            self::VALIDATION_ERROR => 'Validation failed for the provided data',
            self::ALREADY_EXISTS => 'Resource already exists',
            self::DATABASE_ERROR => 'A database error occurred',
            self::UNAUTHORIZED => 'Unauthorized access',
            self::UNAUTHENTICATED => 'Unauthorized access',
            self::FORBIDDEN => 'Access forbidden to this resource',
            self::EXPIRED => 'Token has expired',
            self::INVALID_TOKEN => 'Invalid token',
            self::AUTH_FAILURE => 'Authentication failed',
            self::ERROR => 'An error occurred',
        };
    }
}

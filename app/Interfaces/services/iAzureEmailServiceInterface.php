<?php

namespace App\Interfaces\services;

interface iAzureEmailServiceInterface
{
    /**
     * Get access token for Microsoft Graph API
     */
    public function getAccessToken(): string;

    /**
     * Fetch emails from Office 365 mailbox
     */
    public function fetchEmails(string $supportEmail, int $limit): array;

    /**
     * Mark email as read
     */
    public function markEmailAsRead(string $emailId): bool;

    /**
     * Check if authentication is valid
     */
    public function hasValidToken(): bool;
}

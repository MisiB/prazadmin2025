<?php

namespace App\Services;

use App\Interfaces\services\iAzureEmailServiceInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class AzureEmailService implements iAzureEmailServiceInterface
{
    protected string $clientId;

    protected string $tenantId;

    protected string $clientSecret;

    protected \GuzzleHttp\Client $client;

    public function __construct()
    {
        $this->clientId = config('services.msgraph.client_id') ?? '';
        $this->tenantId = config('services.msgraph.tenant_id') ?? '';
        $this->clientSecret = config('services.msgraph.client_secret') ?? '';

        // Configure GuzzleHttp client with SSL options for local development
        $this->client = new \GuzzleHttp\Client([
            'verify' => config('app.env') === 'local' ? false : true,
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Get access token for Microsoft Graph API
     */
    public function getAccessToken(): string
    {
        try {
            $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";

            $response = $this->client->post($url, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default',
                ],
            ]);

            $body = json_decode($response->getBody());

            if (! isset($body->access_token)) {
                throw new \Exception('Access token not found in response');
            }

            return $body->access_token;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error('Connection error while getting Azure access token', [
                'error' => $e->getMessage(),
                'tenant_id' => $this->tenantId,
                'client_id' => $this->clientId,
            ]);
            throw new \Exception('Unable to connect to Azure authentication service. Please check your internet connection and SSL configuration.');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('Request error while getting Azure access token', [
                'error' => $e->getMessage(),
                'tenant_id' => $this->tenantId,
                'client_id' => $this->clientId,
            ]);
            throw new \Exception('Failed to authenticate with Azure: '.$e->getMessage());
        } catch (\Exception $e) {
            Log::error('Failed to get Azure access token', [
                'error' => $e->getMessage(),
                'tenant_id' => $this->tenantId,
                'client_id' => $this->clientId,
            ]);
            throw $e;
        }
    }

    /**
     * Fetch emails from Office 365 mailbox
     */
    public function fetchEmails(string $supportEmail, int $limit): array
    {
        try {
            $accessToken = $this->getAccessToken();
            $url = "https://graph.microsoft.com/v1.0/users/{$supportEmail}/messages";

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    '$top' => $limit,
                    '$orderby' => 'receivedDateTime desc',
                    '$select' => 'id,subject,body,from,receivedDateTime,isRead,hasAttachments',
                    '$expand' => 'attachments',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new \Exception("HTTP {$statusCode}: Failed to fetch emails");
            }

            $data = json_decode($response->getBody(), true);

            if (! isset($data['value'])) {
                return [];
            }

            $emails = [];
            foreach ($data['value'] as $message) {
                // Only process unread emails
                if (! ($message['isRead'] ?? false)) {
                    $attachments = [];

                    // Process attachments if they exist
                    if (isset($message['attachments']) && is_array($message['attachments'])) {
                        foreach ($message['attachments'] as $attachment) {
                            $attachments[] = [
                                'id' => $attachment['id'] ?? '',
                                'name' => $attachment['name'] ?? 'unknown',
                                'contentType' => $attachment['contentType'] ?? 'application/octet-stream',
                                'size' => $attachment['size'] ?? 0,
                                'contentBytes' => $attachment['contentBytes'] ?? '',
                            ];
                        }
                    }

                    $emails[] = [
                        'id' => $message['id'],
                        'subject' => $message['subject'] ?? 'No Subject',
                        'body' => $message['body']['content'] ?? '',
                        'sender_email' => $message['from']['emailAddress']['address'] ?? '',
                        'sender_name' => $message['from']['emailAddress']['name'] ?? '',
                        'received_at' => $message['receivedDateTime'] ?? now(),
                        'is_read' => $message['isRead'] ?? false,
                        'has_attachments' => $message['hasAttachments'] ?? false,
                        'attachments' => $attachments,
                    ];
                }
            }

            return $emails;

        } catch (GuzzleException $e) {
            Log::error('Guzzle exception while fetching emails', [
                'error' => $e->getMessage(),
                'support_email' => $supportEmail,
                'limit' => $limit,
            ]);
            throw new \Exception("Failed to fetch emails: {$e->getMessage()}");
        } catch (\Exception $e) {
            Log::error('Error fetching emails', [
                'error' => $e->getMessage(),
                'support_email' => $supportEmail,
                'limit' => $limit,
            ]);
            throw $e;
        }
    }

    /**
     * Mark email as read
     */
    public function markEmailAsRead(string $emailId): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            $url = "https://graph.microsoft.com/v1.0/me/messages/{$emailId}";

            $response = $this->client->patch($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'isRead' => true,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            return $statusCode === 200;

        } catch (GuzzleException $e) {
            Log::warning('Failed to mark email as read', [
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::warning('Failed to mark email as read', [
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if authentication is valid
     */
    public function hasValidToken(): bool
    {
        try {
            $accessToken = $this->getAccessToken();
            // Use a simple endpoint that works with client credentials flow
            $url = 'https://graph.microsoft.com/v1.0/users';

            $response = $this->client->get($url, [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'query' => [
                    '$top' => 1, // Just get one user to test the token
                ],
            ]);

            return $response->getStatusCode() === 200;

        } catch (\Exception $e) {
            Log::debug('Token validation failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

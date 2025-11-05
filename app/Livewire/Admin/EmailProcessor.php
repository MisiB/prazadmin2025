<?php

namespace App\Livewire\Admin;

use App\Interfaces\repositories\iissuegroupInterface;
use App\Interfaces\repositories\iissuelogInterface;
use App\Interfaces\repositories\iissuetypeInterface;
use App\Models\Issuelog;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Prism\Prism\Tool;

#[Layout('components.layouts.app')]
class EmailProcessor extends Component
{
    public string $supportEmail = '';

    public int $emailLimit = 100;

    public bool $isProcessing = false;

    public bool $isAuthenticated = false;

    public array $emails = [];

    public array $processedEmails = [];

    public string $statusMessage = '';

    public string $errorMessage = '';

    public bool $showAnalysis = false;

    public array $selectedEmail = [];

    public array $analysisResult = [];

    public bool $autoProcessingEnabled = true;

    public int $autoProcessingInterval = 3; // minutes

    public string $lastAutoProcessTime = '';

    protected iissuelogInterface $issueLogRepo;

    protected iissuetypeInterface $issueTypeRepo;

    protected iissuegroupInterface $issueGroupRepo;

    public function boot(
        iissuelogInterface $issueLogRepo,
        iissuetypeInterface $issueTypeRepo,
        iissuegroupInterface $issueGroupRepo
    ): void {
        $this->issueLogRepo = $issueLogRepo;
        $this->issueTypeRepo = $issueTypeRepo;
        $this->issueGroupRepo = $issueGroupRepo;
    }

    public function mount(): void
    {
        $this->supportEmail = config('services.support_email');
        $this->checkAuthentication();
        $this->autoProcessEmails();
    }

    public function checkAuthentication(): void
    {
        try {
            // Check if connected first to avoid redirect being returned
            if (! MsGraph::isConnected()) {
                $this->isAuthenticated = false;
                $this->statusMessage = 'Microsoft Graph authentication required.';
                $this->errorMessage = 'Please authenticate at: '.config('app.url').'/connect';

                return;
            }

            // Get access token with redirect disabled to avoid redirect object
            $token = MsGraph::getAccessToken(null, false);
            if ($token === null || ! is_string($token)) {
                $this->isAuthenticated = false;
                $this->statusMessage = 'Microsoft Graph authentication required.';
                $this->errorMessage = 'Please authenticate at: '.config('app.url').'/connect';

                return;
            }

            // Try to verify the token by making a simple API call
            MsGraph::get('me');
            $this->isAuthenticated = true;
            $this->statusMessage = 'Microsoft Graph authentication is active.';
        } catch (\Exception $e) {
            $this->isAuthenticated = false;
            $this->statusMessage = 'Microsoft Graph authentication required.';
            $this->errorMessage = 'Please authenticate at: '.config('app.url').'/connect';
        }
    }

    public function fetchEmails(): void
    {
        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate Microsoft Graph first.';

            return;
        }

        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->statusMessage = 'Fetching emails...';

        try {
            $response = MsGraph::get("users/{$this->supportEmail}/messages", [
                '$top' => $this->emailLimit,
                '$orderby' => 'receivedDateTime desc',
                '$select' => 'id,subject,body,from,receivedDateTime,isRead,hasAttachments',
                '$expand' => 'attachments',
            ]);

            // Check if response contains HTML (indicates authentication issue)
            if (is_string($response) && str_contains($response, '<!DOCTYPE html>')) {
                $this->isAuthenticated = false;
                $this->errorMessage = 'Authentication expired. Please re-authenticate.';
                $this->statusMessage = 'Authentication required.';

                return;
            }

            $this->emails = [];
            foreach ($response['value'] ?? [] as $message) {
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

                $this->emails[] = [
                    'id' => $message['id'],
                    'subject' => $message['subject'] ?? 'No Subject',
                    'body' => $message['body']['content'] ?? '',
                    'sender_email' => $message['from']['emailAddress']['address'] ?? '',
                    'sender_name' => $message['from']['emailAddress']['name'] ?? '',
                    'received_at' => $message['receivedDateTime'] ?? now(),
                    'is_read' => $message['isRead'] ?? false,
                    'has_attachments' => $message['hasAttachments'] ?? false,
                    'attachments' => $attachments,
                    'processed' => false,
                ];
            }

            $this->statusMessage = 'Found '.count($this->emails).' emails.';
            $this->processedEmails = [];

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, '302 Found') || str_contains($errorMessage, 'connect')) {
                $this->isAuthenticated = false;
                $this->errorMessage = 'Microsoft Graph authentication required or expired.';
                $this->statusMessage = 'Please authenticate at: '.config('app.url').'/connect';
            } else {
                $this->errorMessage = 'Failed to fetch emails: '.$errorMessage;
                $this->statusMessage = 'Error occurred while fetching emails.';
            }

            Log::error('Email fetch error', [
                'support_email' => $this->supportEmail,
                'error' => $errorMessage,
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }

    public function analyzeEmail(int $index): void
    {
        if (! isset($this->emails[$index])) {
            return;
        }

        $email = $this->emails[$index];
        $this->selectedEmail = $email;

        try {
            $agent = $this->initializeAgent();
            $this->analysisResult = $this->analyzeEmailWithAgent($agent, $email);
            $this->showAnalysis = true;
        } catch (\Exception $e) {
            $this->errorMessage = 'Analysis failed: '.$e->getMessage();
        }
    }

    public function createTicket(int $index): void
    {
        if (! isset($this->emails[$index])) {
            return;
        }

        $email = $this->emails[$index];

        try {
            $agent = $this->initializeAgent();
            $analysis = $this->analyzeEmailWithAgent($agent, $email);

            if ($analysis['should_create_ticket']) {
                $ticket = $this->createTicketFromEmail($email, $analysis);

                if ($ticket) {
                    $this->emails[$index]['processed'] = true;
                    $this->processedEmails[] = [
                        'email' => $email,
                        'ticket' => $ticket,
                        'analysis' => $analysis,
                    ];

                    $this->statusMessage = "Created ticket: {$ticket->ticketnumber}";
                    $this->markEmailAsRead($email['id']);
                } else {
                    $this->errorMessage = 'Failed to create ticket.';
                }
            } else {
                $this->errorMessage = 'Email does not require ticket creation.';
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to process email: '.$e->getMessage();
            Log::error('Email processing error', [
                'email_subject' => $email['subject'],
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function processAllEmails(): void
    {
        if (! $this->isAuthenticated) {
            $this->errorMessage = 'Please authenticate Microsoft Graph first.';

            return;
        }

        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->statusMessage = 'Processing all emails...';

        $processedCount = 0;
        $createdTicketsCount = 0;

        try {
            $agent = $this->initializeAgent();

            foreach ($this->emails as $index => $email) {
                if ($email['processed']) {
                    continue;
                }

                try {
                    $analysis = $this->analyzeEmailWithAgent($agent, $email);

                    if ($analysis['should_create_ticket']) {
                        $ticket = $this->createTicketFromEmail($email, $analysis);
                        if ($ticket) {
                            $this->emails[$index]['processed'] = true;
                            $this->processedEmails[] = [
                                'email' => $email,
                                'ticket' => $ticket,
                                'analysis' => $analysis,
                            ];
                            $createdTicketsCount++;
                            $this->markEmailAsRead($email['id']);
                        }
                    }

                    $processedCount++;

                } catch (\Exception $e) {
                    Log::error('Email processing error', [
                        'email_subject' => $email['subject'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->statusMessage = "Processing complete! Processed: {$processedCount} emails, Created tickets: {$createdTicketsCount}";

        } catch (\Exception $e) {
            $this->errorMessage = 'Processing failed: '.$e->getMessage();
            Log::error('Batch email processing error', [
                'error' => $e->getMessage(),
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }

    public function refreshToken(): void
    {
        try {
            // This would typically call the msgraph:keep-alive command
            // For now, we'll just check authentication again
            $this->checkAuthentication();
            $this->statusMessage = 'Token refresh attempted.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Token refresh failed: '.$e->getMessage();
        }
    }

    public function clearMessages(): void
    {
        $this->errorMessage = '';
        $this->statusMessage = '';
    }

    public function closeAnalysis(): void
    {
        $this->showAnalysis = false;
        $this->selectedEmail = [];
        $this->analysisResult = [];
    }

    public function toggleAutoProcessing(): void
    {
        $this->autoProcessingEnabled = ! $this->autoProcessingEnabled;

        if ($this->autoProcessingEnabled) {
            $this->statusMessage = 'Auto-processing enabled. Will check emails every '.$this->autoProcessingInterval.' minutes.';
            $this->startAutoProcessing();
        } else {
            $this->statusMessage = 'Auto-processing disabled.';
        }
    }

    public function startAutoProcessing(): void
    {
        if (! $this->autoProcessingEnabled) {
            return;
        }

        $this->dispatch('start-auto-processing', [
            'interval' => $this->autoProcessingInterval * 60 * 1000, // Convert to milliseconds
        ]);
    }

    public function stopAutoProcessing(): void
    {
        $this->autoProcessingEnabled = false;
        $this->dispatch('stop-auto-processing');
        $this->statusMessage = 'Auto-processing stopped.';
    }

    public function autoProcessEmails(): void
    {
        if (! $this->autoProcessingEnabled || ! $this->isAuthenticated) {
            return;
        }

        $this->lastAutoProcessTime = now()->format('H:i:s');
        $this->statusMessage = 'Auto-processing emails...';

        try {
            // Fetch new emails
            $this->fetchEmails();

            if (empty($this->emails)) {
                $this->statusMessage = 'No new emails found for auto-processing.';

                return;
            }

            // Process unprocessed emails
            $processedCount = 0;
            $createdTicketsCount = 0;

            foreach ($this->emails as $index => $email) {
                if ($email['processed']) {
                    continue;
                }

                try {
                    $analysis = $this->analyzeEmailContent(
                        $email['body'],
                        $email['subject'],
                        $email['sender_email']
                    );

                    // Always create ticket for emails that reach this point
                    // If analysis is inconclusive, classify as General
                    if (! $analysis['should_create_ticket']) {
                        $analysis['should_create_ticket'] = true;
                        $analysis['issue_type'] = 'General';
                        $analysis['priority'] = 'medium';
                        $analysis['confidence_score'] = 50; // Low confidence for auto-classification
                    }

                    $ticket = $this->createTicketFromEmail($email, $analysis);

                    if ($ticket) {
                        $this->emails[$index]['processed'] = true;
                        $this->processedEmails[] = [
                            'email' => $email,
                            'ticket' => $ticket,
                            'analysis' => $analysis,
                        ];
                        $createdTicketsCount++;
                        $this->markEmailAsRead($email['id']);
                    }

                    $processedCount++;

                } catch (\Exception $e) {
                    Log::error('Auto-processing email error', [
                        'email_subject' => $email['subject'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->statusMessage = "Auto-processing complete! Processed: {$processedCount} emails, Created tickets: {$createdTicketsCount}";

        } catch (\Exception $e) {
            $this->errorMessage = 'Auto-processing failed: '.$e->getMessage();
            Log::error('Auto-processing error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Initialize PrismPHP agent
     */
    private function initializeAgent(): Tool
    {
        return (new Tool)
            ->as('analyze_email_for_ticket')
            ->for('Analyzes email content to determine if it should create a support ticket and extracts relevant information')
            ->withStringParameter('email_content', 'The full content of the email')
            ->withStringParameter('email_subject', 'The subject line of the email')
            ->withStringParameter('sender_email', 'The email address of the sender')
            ->using(function (string $emailContent, string $emailSubject, string $senderEmail): string {
                $analysis = $this->analyzeEmailContent($emailContent, $emailSubject, $senderEmail);

                return json_encode($analysis);
            });
    }

    /**
     * Analyze email using PrismPHP agent
     */
    private function analyzeEmailWithAgent(Tool $agent, array $email): array
    {
        $result = $agent->handle(
            $email['body'],
            $email['subject'],
            $email['sender_email']
        );

        return json_decode($result, true);
    }

    /**
     * Analyze email content
     */
    private function analyzeEmailContent(string $content, string $subject, string $sender): array
    {
        // Clean HTML from content and subject for analysis
        $cleanContent = strip_tags($content);
        $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES, 'UTF-8');

        $cleanSubject = strip_tags($subject);
        $cleanSubject = html_entity_decode($cleanSubject, ENT_QUOTES, 'UTF-8');

        $supportKeywords = [
            'help', 'support', 'issue', 'problem', 'error', 'bug', 'broken',
            'not working', 'unable to', 'cannot', 'failed', 'trouble',
            'assistance', 'urgent', 'critical', 'emergency',
        ];

        $contentLower = strtolower($cleanContent.' '.$cleanSubject);

        $keywordMatches = 0;
        foreach ($supportKeywords as $keyword) {
            if (strpos($contentLower, $keyword) !== false) {
                $keywordMatches++;
            }
        }

        $shouldCreateTicket = $keywordMatches > 0 ||
                            strpos($contentLower, 'support') !== false ||
                            strpos($contentLower, 'help') !== false;

        $priority = 'medium';
        if (strpos($contentLower, 'urgent') !== false ||
            strpos($contentLower, 'critical') !== false ||
            strpos($contentLower, 'emergency') !== false) {
            $priority = 'high';
        } elseif (strpos($contentLower, 'low') !== false ||
                 strpos($contentLower, 'minor') !== false) {
            $priority = 'low';
        }

        $issueType = $this->determineIssueType($contentLower);

        return [
            'should_create_ticket' => $shouldCreateTicket,
            'priority' => $priority,
            'issue_type' => $issueType,
            'extracted_title' => $this->extractTitle($subject, $content),
            'extracted_description' => $this->cleanDescription($content),
            'confidence_score' => min(($keywordMatches / count($supportKeywords)) * 100, 100),
        ];
    }

    /**
     * Determine issue type based on content
     */
    private function determineIssueType(string $content): string
    {
        if (strpos($content, 'login') !== false || strpos($content, 'password') !== false) {
            return 'Authentication';
        } elseif (strpos($content, 'email') !== false || strpos($content, 'mail') !== false) {
            return 'Email';
        } elseif (strpos($content, 'system') !== false || strpos($content, 'server') !== false) {
            return 'System';
        } elseif (strpos($content, 'database') !== false || strpos($content, 'data') !== false) {
            return 'Database';
        } elseif (strpos($content, 'network') !== false || strpos($content, 'connection') !== false) {
            return 'Network';
        } elseif (strpos($content, 'payment') !== false || strpos($content, 'paynow') !== false) {
            return 'Finance';
        } elseif (strpos($content, 'registration') !== false || strpos($content, 'approval') !== false) {
            return 'Operations';
        } else {
            return 'General';
        }
    }

    /**
     * Extract title from subject or content
     */
    private function extractTitle(string $subject, string $content): string
    {
        // Clean HTML from subject
        $cleanSubject = strip_tags($subject);
        $cleanSubject = html_entity_decode($cleanSubject, ENT_QUOTES, 'UTF-8');

        if (strlen($cleanSubject) > 10 && strlen($cleanSubject) < 100) {
            return $cleanSubject;
        }

        // Clean HTML from content and extract first sentence
        $cleanContent = strip_tags($content);
        $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES, 'UTF-8');

        $sentences = preg_split('/[.!?]+/', $cleanContent);
        $firstSentence = trim($sentences[0] ?? '');

        return strlen($firstSentence) > 100 ? substr($firstSentence, 0, 97).'...' : $firstSentence;
    }

    /**
     * Clean email description
     */
    private function cleanDescription(string $content): string
    {
        // Remove HTML tags and decode HTML entities
        $content = strip_tags($content);
        $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

        // Remove email headers and signatures
        $content = preg_replace('/^.*?(?=\n\n)/s', '', $content);
        $content = preg_replace('/\n--.*$/s', '', $content);
        $content = preg_replace('/\n>.*$/s', '', $content);

        // Clean up whitespace
        $content = preg_replace('/\s+/', ' ', $content); // Replace multiple whitespace with single space
        $content = trim($content);

        return strlen($content) > 1000 ? substr($content, 0, 997).'...' : $content;
    }

    /**
     * Create ticket from analyzed email
     */
    private function createTicketFromEmail(array $email, array $analysis): ?Issuelog
    {
        try {
            $issueGroups = $this->issueGroupRepo->getIssueGroups();
            $defaultIssueGroup = $issueGroups->first();

            if (! $defaultIssueGroup) {
                $this->errorMessage = 'No issue groups found. Please create issue groups first.';

                return null;
            }

            $issueTypes = $this->issueTypeRepo->getIssueTypes();
            $defaultIssueType = $issueTypes->first();

            if (! $defaultIssueType) {
                $this->errorMessage = 'No issue types found. Please create issue types first.';

                return null;
            }

            $ticketNumber = 'TKT-'.date('Y').'-'.str_pad(
                (Issuelog::count() + 1),
                6,
                '0',
                STR_PAD_LEFT
            );

            $ticketData = [
                'issuegroup_id' => $defaultIssueGroup->id,
                'issuetype_id' => $defaultIssueType->id,
                'ticketnumber' => $ticketNumber,
                'regnumber' => $ticketNumber,
                'name' => $email['sender_name'] ?: 'Unknown',
                'email' => $email['sender_email'],
                'phone' => $email['sender_email'],
                'title' => $analysis['extracted_title'] ?: $email['subject'],
                'description' => $analysis['extracted_description'] ?: $email['body'],
                'attachments' => $this->processEmailAttachments($email['attachments'] ?? []),
                'status' => 'open',
                'priority' => $analysis['priority'],
                'assigned_to' => null,
                'assigned_by' => null,
                'assigned_at' => null,
                'created_by' => 'system',
            ];

            $result = $this->issueLogRepo->createissuelog($ticketData);

            if ($result['status'] === 'success') {
                // Return the actual Issuelog model
                return Issuelog::find($result['data']['id']);
            } else {
                $this->errorMessage = $result['message'];

                return null;
            }

        } catch (\Exception $e) {
            Log::error('Ticket creation error', [
                'email_subject' => $email['subject'],
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Process email attachments for ticket creation
     */
    private function processEmailAttachments(array $attachments): array
    {
        $processedAttachments = [];

        foreach ($attachments as $attachment) {
            try {
                // Skip if no content bytes
                if (empty($attachment['contentBytes'])) {
                    continue;
                }

                // Decode base64 content
                $fileData = base64_decode($attachment['contentBytes']);

                if ($fileData === false) {
                    Log::warning('Failed to decode attachment', [
                        'attachment_name' => $attachment['name'],
                    ]);

                    continue;
                }

                // Validate file size (max 10MB)
                if (strlen($fileData) > 10 * 1024 * 1024) {
                    Log::warning('Attachment too large, skipping', [
                        'attachment_name' => $attachment['name'],
                        'size' => strlen($fileData),
                    ]);

                    continue;
                }

                // Validate file type
                $allowedTypes = [
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                    'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/plain', 'text/csv', 'application/zip', 'application/x-zip-compressed',
                ];

                if (! in_array($attachment['contentType'], $allowedTypes)) {
                    Log::warning('Unsupported attachment type', [
                        'attachment_name' => $attachment['name'],
                        'content_type' => $attachment['contentType'],
                    ]);

                    continue;
                }

                $processedAttachments[] = [
                    'name' => $attachment['name'],
                    'data' => $attachment['contentBytes'], // Keep as base64 for repository processing
                    'mime_type' => $attachment['contentType'],
                    'size' => strlen($fileData),
                ];

            } catch (\Exception $e) {
                Log::error('Error processing attachment', [
                    'attachment_name' => $attachment['name'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processedAttachments;
    }

    /**
     * Mark email as read in Office 365
     */
    private function markEmailAsRead(string $emailId): void
    {
        try {
            MsGraph::patch("me/messages/{$emailId}", [
                'isRead' => true,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to mark email as read', [
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.email-processor');
    }
}

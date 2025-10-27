<?php

namespace App\Console\Commands;

use App\Interfaces\repositories\iissuegroupInterface;
use App\Interfaces\repositories\iissuelogInterface;
use App\Interfaces\repositories\iissuetypeInterface;
use App\Interfaces\services\iAzureEmailServiceInterface;
use App\Models\Issuelog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Tool;

class ProcessSupportEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-support-emails 
                            {--support-email= : The support email address to monitor}
                            {--limit=10 : Maximum number of emails to process}
                            {--dry-run : Run without creating tickets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process emails from Office 365 support inbox and create tickets using PrismPHP AI agent';

    protected iissuelogInterface $issueLogRepo;

    protected iissuetypeInterface $issueTypeRepo;

    protected iissuegroupInterface $issueGroupRepo;

    protected iAzureEmailServiceInterface $azureEmailService;

    public function __construct(
        iissuelogInterface $issueLogRepo,
        iissuetypeInterface $issueTypeRepo,
        iissuegroupInterface $issueGroupRepo,
        iAzureEmailServiceInterface $azureEmailService
    ) {
        parent::__construct();
        $this->issueLogRepo = $issueLogRepo;
        $this->issueTypeRepo = $issueTypeRepo;
        $this->issueGroupRepo = $issueGroupRepo;
        $this->azureEmailService = $azureEmailService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $supportEmail = $this->option('support-email') ?? 'misib@praz.org.zw';
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info("Starting email processing for: {$supportEmail}");
        $this->info("Limit: {$limit} emails");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No tickets will be created');
        }

        try {
            // Initialize PrismPHP agent
            $agent = $this->initializeAgent();

            // Get emails from Office 365
            $emails = $this->fetchEmails($supportEmail, $limit);

            if (empty($emails)) {
                $this->info('No new emails found to process.');

                return Command::SUCCESS;
            }

            $this->info('Found '.count($emails).' emails to process');

            $processedCount = 0;
            $createdTicketsCount = 0;

            foreach ($emails as $email) {
                $this->info("Processing email: {$email['subject']}");

                try {
                    $analysis = $this->analyzeEmailWithAgent($agent, $email);

                    if ($analysis['should_create_ticket']) {
                        if (! $dryRun) {
                            $ticket = $this->createTicketFromEmail($email, $analysis);
                            if ($ticket) {
                                $createdTicketsCount++;
                                $this->info("Created ticket: {$ticket->ticketnumber}");
                            }
                        } else {
                            $this->info("Would create ticket for: {$email['subject']}");
                            $createdTicketsCount++;
                        }
                    } else {
                        $this->info("Email does not require ticket creation: {$email['subject']}");
                    }

                    $processedCount++;

                } catch (\Exception $e) {
                    $this->error("Error processing email '{$email['subject']}': ".$e->getMessage());
                    Log::error('Email processing error', [
                        'email_subject' => $email['subject'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            $this->info('Processing complete!');
            $this->info("Processed: {$processedCount} emails");
            $this->info("Created tickets: {$createdTicketsCount}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Command failed: '.$e->getMessage());
            Log::error('ProcessSupportEmails command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Initialize PrismPHP agent with ticket creation tools
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
                // Analyze email content to determine if it's a support request
                $analysis = $this->analyzeEmailContent($emailContent, $emailSubject, $senderEmail);

                return json_encode($analysis);
            });
    }

    /**
     * Fetch emails from Office 365 using Azure Email Service
     */
    private function fetchEmails(string $supportEmail, int $limit): array
    {
        try {
            // Check if we have a valid token first
            if (! $this->azureEmailService->hasValidToken()) {
                $this->error('Azure authentication required. Please check your Azure configuration.');
                $this->info('Ensure your Azure app registration has proper permissions and credentials.');

                return [];
            }

            // Get emails using the Azure Email Service
            $emails = $this->azureEmailService->fetchEmails($supportEmail, $limit);

            return $emails;

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            $this->error('Failed to fetch emails: '.$errorMessage);
            $this->info('Please check your Azure configuration and permissions.');

            Log::error('Email fetch error', [
                'support_email' => $supportEmail,
                'error' => $errorMessage,
            ]);

            return [];
        }
    }

    /**
     * Mark email as read in Office 365
     */
    private function markEmailAsRead(string $emailId): void
    {
        try {
            $success = $this->azureEmailService->markEmailAsRead($emailId);

            if (! $success) {
                $this->warn('Failed to mark email as read');
            }
        } catch (\Exception $e) {
            $this->warn('Failed to mark email as read: '.$e->getMessage());
            Log::warning('Failed to mark email as read', [
                'email_id' => $emailId,
                'error' => $e->getMessage(),
            ]);
        }
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
     * Analyze email content to determine if it's a support request
     */
    private function analyzeEmailContent(string $content, string $subject, string $sender): array
    {
        // Clean HTML from content and subject for analysis
        $cleanContent = strip_tags($content);
        $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES, 'UTF-8');

        $cleanSubject = strip_tags($subject);
        $cleanSubject = html_entity_decode($cleanSubject, ENT_QUOTES, 'UTF-8');

        // Keywords that indicate support requests
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

        // Determine if this should create a ticket
        $shouldCreateTicket = $keywordMatches > 0 ||
                            strpos($contentLower, 'support') !== false ||
                            strpos($contentLower, 'help') !== false;

        // Extract priority based on keywords
        $priority = 'medium';
        if (strpos($contentLower, 'urgent') !== false ||
            strpos($contentLower, 'critical') !== false ||
            strpos($contentLower, 'emergency') !== false) {
            $priority = 'high';
        } elseif (strpos($contentLower, 'low') !== false ||
                 strpos($contentLower, 'minor') !== false) {
            $priority = 'low';
        }

        // Determine issue type based on content
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

        // Use subject if it's descriptive, otherwise extract from content
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
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);

        return strlen($content) > 1000 ? substr($content, 0, 997).'...' : $content;
    }

    /**
     * Create ticket from analyzed email
     */
    private function createTicketFromEmail(array $email, array $analysis): ?Issuelog
    {
        try {
            // Get default issue group and type
            $issueGroups = $this->issueGroupRepo->getIssueGroups();
            $defaultIssueGroup = $issueGroups->first();

            if (! $defaultIssueGroup) {
                $this->error('No issue groups found. Please create issue groups first.');

                return null;
            }

            $issueTypes = $this->issueTypeRepo->getIssueTypes();
            $defaultIssueType = $issueTypes->first();

            if (! $defaultIssueType) {
                $this->error('No issue types found. Please create issue types first.');

                return null;
            }

            // Generate ticket number
            $ticketNumber = 'TKT-'.date('Y').'-'.str_pad(
                (Issuelog::count() + 1),
                6,
                '0',
                STR_PAD_LEFT
            );

            $ticketData = [
                'issuegroup_id' => $defaultIssueGroup->id,
                'issuetype_id' => $defaultIssueType->id,
                'department_id' => 1, // Default department
                'ticketnumber' => $ticketNumber,
                'regnumber' => $ticketNumber,
                'name' => $email['sender_name'] ?: 'Unknown',
                'email' => $email['sender_email'],
                'phone' => null,
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
                $ticket = Issuelog::find($result['data']['id']);

                // Mark email as read
                $this->markEmailAsRead($email['id']);

                return $ticket;
            } else {
                $this->error('Failed to create ticket: '.$result['message']);

                return null;
            }

        } catch (\Exception $e) {
            $this->error('Failed to create ticket: '.$e->getMessage());
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
                    $this->warn('Failed to decode attachment: '.$attachment['name']);

                    continue;
                }

                // Validate file size (max 10MB)
                if (strlen($fileData) > 10 * 1024 * 1024) {
                    $this->warn('Attachment too large, skipping: '.$attachment['name']);

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
                    $this->warn('Unsupported attachment type: '.$attachment['name'].' ('.$attachment['contentType'].')');

                    continue;
                }

                $processedAttachments[] = [
                    'name' => $attachment['name'],
                    'data' => $attachment['contentBytes'], // Keep as base64 for repository processing
                    'mime_type' => $attachment['contentType'],
                    'size' => strlen($fileData),
                ];

            } catch (\Exception $e) {
                $this->warn('Error processing attachment '.$attachment['name'].': '.$e->getMessage());
            }
        }

        return $processedAttachments;
    }
}

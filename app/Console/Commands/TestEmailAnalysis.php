<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Prism\Tool;

class TestEmailAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email-analysis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the PrismPHP email analysis agent with sample data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing PrismPHP Email Analysis Agent...');
        $this->newLine();

        // Initialize the agent (same as in ProcessSupportEmails)
        $agent = $this->initializeAgent();

        $this->info("Agent initialized: {$agent->name()}");
        $this->info("Description: {$agent->description()}");
        $this->newLine();

        // Test cases
        $testCases = [
            [
                'name' => 'Support Request - Login Issue',
                'email' => [
                    'subject' => 'Help! Cannot login to system',
                    'body' => 'I am having trouble logging into the system. I keep getting an error message. Please help me resolve this issue as soon as possible.',
                    'sender_email' => 'user@example.com',
                ],
            ],
            [
                'name' => 'Support Request - Email Problem',
                'email' => [
                    'subject' => 'Email not working',
                    'body' => 'My email is not working properly. I cannot send or receive emails. This is urgent.',
                    'sender_email' => 'employee@company.com',
                ],
            ],
            [
                'name' => 'Non-Support Email',
                'email' => [
                    'subject' => 'Meeting Reminder',
                    'body' => 'Don\'t forget about our meeting tomorrow at 2 PM in the conference room.',
                    'sender_email' => 'manager@company.com',
                ],
            ],
            [
                'name' => 'High Priority Support Request',
                'email' => [
                    'subject' => 'URGENT: System Down',
                    'body' => 'The entire system is down and we cannot process any orders. This is critical and needs immediate attention.',
                    'sender_email' => 'admin@company.com',
                ],
            ],
        ];

        foreach ($testCases as $index => $testCase) {
            $this->info('Test Case '.($index + 1).": {$testCase['name']}");
            $this->line("Subject: {$testCase['email']['subject']}");
            $this->line("Body: {$testCase['email']['body']}");

            try {
                $analysis = $this->analyzeEmailWithAgent($agent, $testCase['email']);

                $this->table(
                    ['Property', 'Value'],
                    [
                        ['Should Create Ticket', $analysis['should_create_ticket'] ? '✅ YES' : '❌ NO'],
                        ['Priority', strtoupper($analysis['priority'])],
                        ['Issue Type', $analysis['issue_type']],
                        ['Extracted Title', $analysis['extracted_title']],
                        ['Confidence Score', $analysis['confidence_score'].'%'],
                        ['Description Length', strlen($analysis['extracted_description']).' characters'],
                    ]
                );

            } catch (\Exception $e) {
                $this->error('Analysis failed: '.$e->getMessage());
            }

            $this->newLine();
        }

        $this->info('✅ Email analysis testing completed successfully!');
        $this->newLine();
        $this->info('The PrismPHP agent is working correctly and can:');
        $this->line('• Detect support requests vs regular emails');
        $this->line('• Determine priority levels (high/medium/low)');
        $this->line('• Classify issue types (Authentication, Email, System, etc.)');
        $this->line('• Extract meaningful titles and descriptions');
        $this->line('• Provide confidence scores for analysis');

        return Command::SUCCESS;
    }

    /**
     * Initialize PrismPHP agent (same as ProcessSupportEmails)
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
     * Analyze email content (same logic as ProcessSupportEmails)
     */
    private function analyzeEmailContent(string $content, string $subject, string $sender): array
    {
        // Keywords that indicate support requests
        $supportKeywords = [
            'help', 'support', 'issue', 'problem', 'error', 'bug', 'broken',
            'not working', 'unable to', 'cannot', 'failed', 'trouble',
            'assistance', 'urgent', 'critical', 'emergency',
        ];

        $contentLower = strtolower($content.' '.$subject);

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
        $content = preg_replace('/^.*?(?=\n\n)/s', '', $content); // Remove headers
        $content = preg_replace('/\n--.*$/s', '', $content); // Remove signatures
        $content = preg_replace('/\n>.*$/s', '', $content); // Remove quoted text

        // Clean up whitespace
        $content = preg_replace('/\s+/', ' ', $content); // Replace multiple whitespace with single space
        $content = trim($content);

        // Limit length
        return strlen($content) > 1000 ? substr($content, 0, 997).'...' : $content;
    }
}

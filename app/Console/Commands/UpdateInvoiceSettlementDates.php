<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateInvoiceSettlementDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:update-settlement-dates {--dry-run : Show what would be updated without making changes} {--chunk=100 : Number of invoices to process at once} {--commit-chunk=5000 : Number of invoices to commit at once} {--debug : Show detailed debugging information} {--test : Test mode - process only first 10 invoices}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update settled_at column in invoices table based on latest Suspenseutilization created_at date for PAID invoices only';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $isDebug = $this->option('debug');
        $isTest = $this->option('test');
        $chunkSize = (int) $this->option('chunk');
        $commitChunkSize = (int) $this->option('commit-chunk');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        if ($isDebug) {
            $this->info('🐛 DEBUG MODE - Detailed information will be shown');
        }

        if ($isTest) {
            $this->info('🧪 TEST MODE - Processing only first 10 invoices');
        }

        $this->info('🚀 Starting settlement date update process...');
        $this->info("📦 Processing in chunks of {$chunkSize} invoices");
        $this->info("💰 Only processing invoices with status 'PAID'");

        // First, let's check what we're actually dealing with
        $this->info('🔍 Analyzing current data state...');
        
        $totalPaidInvoices = Invoice::where('status', 'PAID')->count();
        $paidWithSuspense = Invoice::where('status', 'PAID')->whereHas('receipts')->count();
        $paidWithSettlementDate = Invoice::where('status', 'PAID')->whereNotNull('settled_at')->count();
        
        $this->info("📊 Total PAID invoices: {$totalPaidInvoices}");
        $this->info("📊 PAID invoices with Suspenseutilization: {$paidWithSuspense}");
        $this->info("📊 PAID invoices with settled_at: {$paidWithSettlementDate}");

        // Get invoice IDs that need updating - using raw query for better performance
        $invoiceIdsQuery = DB::table('invoices')
            ->where('status', 'PAID')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('suspenseutilizations')
                    ->whereColumn('suspenseutilizations.invoice_id', 'invoices.id')
                    ->whereNull('suspenseutilizations.deleted_at');
            })
            ->where(function($query) {
                $query->whereNull('settled_at')
                      ->orWhere('settled_at', '')
                      ->orWhere('settled_at', '0000-00-00 00:00:00');
            })
            ->select('id')
            ->orderBy('id');  // Add this line - required for chunk()
        
        if ($isTest) {
            $invoiceIdsQuery->limit(10);
        }
        
        $totalInvoices = $invoiceIdsQuery->count();
        
        if ($totalInvoices === 0) {
            $this->info('✅ All PAID invoices with Suspenseutilization records already have settled_at set!');
            return Command::SUCCESS;
        }

        $this->info("📊 Found {$totalInvoices} PAID invoices with Suspenseutilization records but no settled_at");

        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $alreadyUpdatedCount = 0;
        $progressBar = $this->output->createProgressBar($totalInvoices);

        try {
            // Process invoice IDs in chunks
            $invoiceIdsQuery->chunk($chunkSize, function ($invoiceIds) use (&$updatedCount, &$skippedCount, &$errorCount, &$alreadyUpdatedCount, $isDryRun, $isDebug, $progressBar) {
                
                foreach ($invoiceIds as $invoiceIdRow) {
                    try {
                        $invoiceId = $invoiceIdRow->id;
                        
                        // Get the latest created_at date from related Suspenseutilization records using raw query
                        $latestSuspense = DB::table('suspenseutilizations')
                            ->where('invoice_id', $invoiceId)
                            ->whereNull('deleted_at')
                            ->orderBy('created_at', 'desc')
                            ->select('created_at')
                            ->first();
                        
                        if ($latestSuspense && $latestSuspense->created_at) {
                            $settlementDate = $latestSuspense->created_at;

                            // Double-check if invoice still needs updating
                            $currentInvoice = DB::table('invoices')
                                ->where('id', $invoiceId)
                                ->select('settled_at', 'invoicenumber')
                                ->first();

                            if ($currentInvoice) {
                                // Check if already updated
                                if ($currentInvoice->settled_at && $currentInvoice->settled_at !== '0000-00-00 00:00:00') {
                                    $alreadyUpdatedCount++;
                                    if ($isDebug) {
                                        $this->line("DEBUG: PAID Invoice #{$invoiceId} - Already has settled_at: {$currentInvoice->settled_at}, skipping");
                                    }
                                    $progressBar->advance();
                                    continue;
                                }

                                if ($isDebug) {
                                    $this->line("DEBUG: PAID Invoice #{$invoiceId} - Found Suspenseutilization with date: {$settlementDate}");
                                    $this->line("DEBUG: Current settled_at: " . ($currentInvoice->settled_at ?? 'NULL'));
                                }

                                if (!$isDryRun) {
                                    // Direct update using query builder for better performance
                                    $result = DB::table('invoices')
                                        ->where('id', $invoiceId)
                                        ->update(['settled_at' => $settlementDate]);
                                    
                                    if ($result) {
                                        $updatedCount++;
                                        
                                        if ($isDebug) {
                                            $this->line("DEBUG: PAID Invoice #{$invoiceId} - Successfully updated");
                                        }
                                    } else {
                                        $errorCount++;
                                        if ($isDebug) {
                                            $this->line("DEBUG: PAID Invoice #{$invoiceId} - Update failed");
                                        }
                                    }
                                } else {
                                    $updatedCount++;
                                    $this->line("Would update PAID Invoice #{$invoiceId} ({$currentInvoice->invoicenumber}) with settled_at: {$settlementDate}");
                                }
                            } else {
                                $skippedCount++;
                                if ($isDebug) {
                                    $this->line("DEBUG: PAID Invoice #{$invoiceId} - Invoice not found");
                                }
                            }
                        } else {
                            $skippedCount++;
                            if ($isDebug) {
                                $this->line("DEBUG: PAID Invoice #{$invoiceId} - No Suspenseutilization records found");
                            }
                        }
                        
                    } catch (\Exception $e) {
                        $errorCount++;
                        if ($isDebug) {
                            $this->line("DEBUG: PAID Invoice #{$invoiceId} - Error: " . $e->getMessage());
                        }
                    }
                    
                    $progressBar->advance();
                    
                    // Force garbage collection every 100 invoices
                    if (($updatedCount + $skippedCount + $errorCount) % 100 === 0) {
                        gc_collect_cycles();
                    }
                }
                
                // Clear memory after each chunk
                unset($invoiceIds);
                gc_collect_cycles();
            });

            $progressBar->finish();
            $this->newLine(2);

            // Now check for PAID invoices without Suspenseutilization records
            $this->info('🔍 Checking for PAID invoices without Suspenseutilization records...');
            $paidWithoutSuspense = Invoice::where('status', 'PAID')
                ->whereDoesntHave('receipts')
                ->count();

            if ($isDryRun) {
                $this->info("✅ DRY RUN COMPLETE");
                $this->info("📈 Would update: {$updatedCount} PAID invoices");
                $this->warn("💡 Run without --dry-run to apply changes");
            } else {
                $this->info("✅ SETTLEMENT DATE UPDATE COMPLETE");
                $this->info("📈 Updated: {$updatedCount} PAID invoices");
                $this->info("⏭️  Skipped: {$skippedCount} PAID invoices (no Suspenseutilization records)");
                $this->info("🔄 Already updated: {$alreadyUpdatedCount} PAID invoices (had settled_at)");
                $this->info("❌ Errors: {$errorCount} PAID invoices");
            }

            // Report on PAID invoices without Suspenseutilization records
            if ($paidWithoutSuspense > 0) {
                $this->warn("⚠️  FOUND {$paidWithoutSuspense} PAID INVOICES WITHOUT SUSPENSEUTILIZATION RECORDS");
                $this->info("💡 These invoices have status 'PAID' but no corresponding payment records");
                $this->info("🔍 This might indicate data inconsistency issues");
            } else {
                $this->info("✅ All PAID invoices have corresponding Suspenseutilization records");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error occurred: " . $e->getMessage());
            $this->info("📈 Successfully updated: {$updatedCount} PAID invoices before error");
            $this->info("⏭️  Skipped: {$skippedCount} PAID invoices");
            $this->info("🔄 Already updated: {$alreadyUpdatedCount} PAID invoices");
            $this->info("❌ Errors: {$errorCount} PAID invoices");
            return Command::FAILURE;
        }
    }
}


<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatamigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command migrates data from the self service database to the main database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Get all the accounts from the self service database

         $this->migrateAccounts();
        // 2. get bank transactions from the self service database
         $this->migrateBankTransactions();
        // 3. get online payments from the self service database
        $this->migrateOnlinePayments();
        // 4. get invoices from the self service database
       $this->migrateInvoices();
        // 5. get epayments from the self service database
         $this->migrateEpayments();

        // 6. get suspenses records
        $this->migrateSuspenses();
        // 7. get  suspenses utilizations invoices records
         $this->migrateSuspenseutilizations();
        // 8. compute suspense balance comparison
        $this->computesuspensebalancecomparison();
    }

    private function migrateAccounts()
    {

        $accounts = DB::connection('selfservicedb')->table('accounts')->get();
        $count = count($accounts);
        $this->info('Total accounts to migrate: '.$count);
        $i = 0;
        DB::beginTransaction();
        try {
            $this->info('Migrating accounts');
            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();
            foreach ($accounts as $account) {
                $i++;
                $customer = DB::table('customers')->where('regnumber', $account->Regnumber)->first();
                if ($customer) {
                   // $this->warn('Account already exists: '.$account->Name.' - '.$i.' of '.$count);
                    $progressBar->advance();

                    continue;
                }
                DB::table('customers')->insert([
                    'id' => $account->id,
                    'regnumber' => $account->Regnumber,
                    'name' => $account->Name,
                    'type' => $account->Type,
                    'country' => $account->country ?? 'ZIMBABWE',
                    'created_at' => $account->created_at,
                    'updated_at' => $account->updated_at,
                ]);
              //  $this->line('Account migrated: '.$account->Name.' - '.$i.' of '.$count);
                $progressBar->advance();
            }
            $progressBar->finish();
            $this->newLine();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
        }
    }

    private function migrateBankTransactions()
    {
        $bankTransactions = DB::connection('selfservicedb')->table('banktransactions')->get();
        $count = count($bankTransactions);
        $this->info('Total bank transactions to migrate: '.$count);
        $i = 0;
        DB::beginTransaction();
        try {
            $this->info('Migrating bank transactions');
            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();
            foreach ($bankTransactions as $bankTransaction) {
                $i++;
                DB::table('banktransactions')->where('id', $bankTransaction->id)->first();
                if ($bankTransaction) {
                    //  $this->warn('Bank transaction already exists: '.$bankTransaction->id.' - '.$i.' of '.$count);
                    $progressBar->advance();

                    continue;
                }
                DB::table('banktransactions')->insert([
                    'id' => $bankTransaction->id,
                    'bank_id' => $bankTransaction->BankId,
                    'customer_id' => $bankTransaction->AccountId,
                    'bankaccount_id' => $bankTransaction->bankaccountId,
                    'referencenumber' => $bankTransaction->Referencenumber,
                    'statementreference' => $bankTransaction->StatementReference,
                    'sourcereference' => $bankTransaction->SourceReference,
                    'description' => $bankTransaction->Description,
                    'accountnumber' => $bankTransaction->Accountnumber,
                    'amount' => $bankTransaction->Amount,
                    'currency' => $bankTransaction->Currency,
                    'regnumber' => $bankTransaction->Regnumber,
                    'status' => $bankTransaction->Status,
                    'copied' => $bankTransaction->Copied,
                    'transactiondate' => $bankTransaction->TransactionDate,
                    'created_at' => $bankTransaction->created_at,
                    'updated_at' => $bankTransaction->updated_at,
                    'user_id' => $bankTransaction->user_id,
                ]);
                $progressBar->advance();
            }
            $progressBar->finish();
            $this->newLine();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
        }
    }

    public function migrateInvoices()
    {
        $invoices = DB::connection('selfservicedb')->table('invoices')->get();
        $count = count($invoices);
        $this->info('Total invoices to migrate: '.$count);
        $i = 0;
        DB::beginTransaction();
        try {
            $this->info('Migrating invoices');
            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();
            foreach ($invoices as $invoice) {
                $i++;
                $check = DB::table('invoices')->where('id', $invoice->id)->first();
                if ($check) {
                    $progressBar->advance();

                    continue;
                }
                DB::table('invoices')->insert([
                    'id' => $invoice->id,
                    'customer_id' => $invoice->AccountId,
                    'currency_id' => $invoice->CurrencyId,
                    'inventoryitem_id' => $invoice->InventoryitemId,
                    'invoicenumber' => $invoice->InvoiceNumber,
                    'amount' => $invoice->Amount,
                    'status' => $invoice->Status,
                    'user_id' => $invoice->user_id,
                    'invoicesource' => $invoice->invoicesource,
                    'invoicetype' => $invoice->invoicetype,
                    'description' => $invoice->description,
                    'source_id' => $invoice->source_id,
                    'exchangerate_id' => $invoice->exchangerate_id,
                    'posted' => $invoice->Posted,
                    'created_at' => $invoice->created_at,
                    'updated_at' => $invoice->updated_at,
                ]);
                $progressBar->advance();
            }
            $progressBar->finish();
            $this->newLine();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
        }
    }

    public function migrateOnlinePayments()
    {
        $onlinePayments = DB::connection('selfservicedb')->table('onlinepayments')->get();
        $count = count($onlinePayments);
        $this->info('Total online payments to migrate: '.$count);
        $i = 0;
        DB::beginTransaction();
        try {
            $this->info('Migrating online payments');
            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();
            foreach ($onlinePayments as $onlinePayment) {
                $i++;
                if ($onlinePayment != null) {
                    $check = DB::table('onlinepayments')->where('id', $onlinePayment->id)->first();
                    if ($check) {

                        $progressBar->advance();

                        continue;
                    }
                    DB::table('onlinepayments')->insert([
                        'id' => $onlinePayment->id,
                        'customer_id' => $onlinePayment->AccountId,
                        'uuid' => $onlinePayment->Uuid,
                        'currency_id' => $onlinePayment->CurrencyId,
                        'amount' => $onlinePayment->Amount,
                        'status' => $onlinePayment->Status,
                        'email' => $onlinePayment->Email,
                        'invoicenumber' => $onlinePayment->InvoiceNumber,
                        'poll_url' => $onlinePayment->PollUrl,
                        'return_url' => $onlinePayment->ReturnUrl,
                        'posted' => $onlinePayment->Posted,
                        'method' => $onlinePayment->Method,
                        'created_at' => $onlinePayment->created_at,
                        'updated_at' => $onlinePayment->updated_at,
                    ]);

                    $progressBar->advance();
                }
            }
            $progressBar->finish();
            $this->newLine();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e);
        }
    }

    public function migrateEpayments()
    {
        $epayments = DB::connection('selfservicedb')->table('epayments')->get();
        $count = count($epayments);
        $this->info('Total epayments to migrate: '.$count);
        $i = 0;
        DB::beginTransaction();
        try {
            $this->info('Migrating epayments');
            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();
            foreach ($epayments as $epayment) {
                $i++;
                $check = DB::table('epayments')->where('id', $epayment->id)->first();
                if ($check) {
                    $progressBar->advance();

                    continue;
                }
                DB::table('epayments')->insert([
                    'id' => $epayment->id,
                    'customer_id' => $epayment->AccountId,
                    'invoice_id' => $epayment->InvoiceId,
                    'onlinepayment_id' => $epayment->onlinepayment_id,
                    'initiation_id' => $epayment->InitiationId,
                    'transactiondate' => $epayment->TransactionDate,
                    'reference' => $epayment->Reference,
                    'bank_id' => $epayment->BankId,
                    'currency' => $epayment->Currency,
                    'amount' => $epayment->Amount,
                    'source' => $epayment->Source,
                    'status' => $epayment->Status,
                    'created_at' => $epayment->created_at,
                    'updated_at' => $epayment->updated_at,
                ]);
                $progressBar->advance();
            }
            $progressBar->finish();
            $this->newLine();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e);
        }
    }

    public function migrateSuspenses()
    {
        $suspenses = DB::connection('selfservicedb')->table('suspenses')->get();
        $count = count($suspenses);
        $this->info('Total suspenses to migrate: '.$count);
        $i = 0;
        DB::beginTransaction();
        try {
            $this->info('Migrating suspenses');
            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();
            foreach ($suspenses as $suspense) {
                $i++;
                $check = DB::table('suspenses')->where('id', $suspense->id)->first();
                if ($check) {
                    $progressBar->advance();

                    continue;
                }
                DB::table('suspenses')->insert([
                    'id' => $suspense->id,
                    'customer_id' => $suspense->AccountId,
                    'sourcetype' => $suspense->Sourcetype,
                    'source_id' => $suspense->SourceId,
                    'currency' => $suspense->Currency,
                    'type' => $suspense->Type,
                    'accountnumber' => $suspense->Accountnumber,
                    'amount' => $suspense->Amount,
                    'status' => $suspense->Status,
                    'posted' => $suspense->Posted,
                    'created_at' => $suspense->created_at,
                    'updated_at' => $suspense->updated_at,
                ]);
                $progressBar->advance();
            }
            $progressBar->finish();
            $this->newLine();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e);
        }
    }

    public function migrateSuspenseutilizations()
    {
        $suspenseutilizations = DB::connection('selfservicedb')->table('suspenseutilizations')->get();
        $count = count($suspenseutilizations);
        $this->info('Total suspenseutilizations to migrate: '.$count);
        $i = 0;
        DB::beginTransaction();
        try {
            $this->info('Migrating suspenseutilizations');
            $progressBar = $this->output->createProgressBar($count);
            $progressBar->start();
            foreach ($suspenseutilizations as $suspenseutilization) {
                $i++;
                $check = DB::table('suspenseutilizations')->where('id', $suspenseutilization->id)->first();
                if ($check) {
                    $progressBar->advance();

                    continue;
                }
                DB::table('suspenseutilizations')->insert([
                    'id' => $suspenseutilization->id,
                    'suspense_id' => $suspenseutilization->SuspenseId,
                    'invoice_id' => $suspenseutilization->InvoiceId,
                    'receiptnumber' => $suspenseutilization->Receiptnumber,
                    'amount' => $suspenseutilization->Amount,
                    'user_id' => $suspenseutilization->user_id,
                    'created_at' => $suspenseutilization->created_at,
                    'updated_at' => $suspenseutilization->updated_at,
                ]);
                $progressBar->advance();
            }
            $progressBar->finish();
            $this->newLine();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e);
        }
        $progressBar->finish();
        $this->newLine();
        DB::commit();

    }

    public function computesuspensebalancecomparison(): void
    {
        $this->info('Computing suspense balance comparison');

        try {
           $suspenses = DB::connection('selfservicedb')->table('suspenses')->get();
           $count = count($suspenses);
           $progressBar = $this->output->createProgressBar($count);
           $progressBar->start();
           $this->info('Total suspenses to compute: '.$count);
           $i = 0;
           $array = [];
          foreach ($suspenses as $suspense) {
            $selfserviceutilizations = DB::connection('selfservicedb')->table('suspenseutilizations')->where('SuspenseId', $suspense->id)->get();
            $selfserviceutilizationsum = $selfserviceutilizations->sum('Amount');
            $selfservicebalance = $suspense->Amount - $selfserviceutilizationsum;
            $maindbsuspense = DB::table('suspenses')->where('id', $suspense->id)->first();
            if ($maindbsuspense) {
                $maindbutilizations = DB::table('suspenseutilizations')->where('suspense_id', $maindbsuspense->id)->get();
                $maindbutilizationsum = $maindbutilizations->sum('amount');
                $maindbbalance = $maindbsuspense->amount - $maindbutilizationsum;
                $difference = $selfservicebalance - $maindbbalance;
                $this->info('Suspense: '.$suspense->id.' - Self service balance: '.$selfservicebalance.' - Main database balance: '.$maindbbalance.' - Difference: '.$difference);
                $array[] = [
                    'suspense_id' => $suspense->id,
                    'selfserviceamount' => $suspense->Amount,
                    'selfserviceutilizations' => $selfserviceutilizationsum,                   
                    'maindbamount' => $maindbsuspense->amount,
                    'maindbutilizations' => $maindbutilizationsum,
                    'selfservicebalance' => $selfservicebalance,
                    'maindbbalance' => $maindbbalance,
                    'difference' => $difference,
                ];
                $progressBar->advance();
            }

                   $progressBar->advance();
          }
          Log::info(json_encode($array));
          $progressBar->finish();
          
        
        } catch (\Exception $e) {
            $this->error('Error computing suspense balance comparison: '.$e->getMessage());
            Log::error('Suspense balance comparison error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

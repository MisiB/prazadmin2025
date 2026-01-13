<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ibanktransactionInterface;
use Carbon\Carbon;
use Livewire\Component;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Transactionreport extends Component
{
    use Toast;

    public $startdate = null;

    public $enddate = null;

    public $bankaccount = null;

    public $modal = false;

    protected $bankaccountrepo;

    protected $banktransactionrepo;

    public function boot(ibankaccountInterface $bankaccountrepo, ibanktransactionInterface $banktransactionrepo)
    {
        $this->bankaccountrepo = $bankaccountrepo;
        $this->banktransactionrepo = $banktransactionrepo;
    }

    public function mount()
    {
        $this->startdate = Carbon::now()->addDays(-7)->format('Y-m-d');
        $this->enddate = Carbon::now()->format('Y-m-d');
    }

    protected function getTransactions()
    {
        try {
            return $this->banktransactionrepo->gettransactionbydaterange($this->startdate, $this->enddate, $this->bankaccount) ?? collect();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load transactions: '.$e->getMessage());

            return collect();
        }
    }

    public function getBankAccounts()
    {
        return $this->bankaccountrepo->getbankaccounts();
    }

    public function retriverecords()
    {
        $this->validate(
            [
                'startdate' => 'required|date',
                'enddate' => 'required|date|after_or_equal:startdate',
            ]
        );

        $this->modal = false;
    }

    public function headers(): array
    {
        return [
            ['key' => 'transactiondate', 'label' => 'Date'],
            ['key' => 'customer.name', 'label' => 'Customer'],
            ['key' => 'accountnumber', 'label' => 'Account Number'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'sourcereference', 'label' => 'Source Ref'],
            ['key' => 'statementreference', 'label' => 'Statement Ref'],
            ['key' => 'referencenumber', 'label' => 'Reference'],
            ['key' => 'currency', 'label' => 'Currency'],
            ['key' => 'amount', 'label' => 'Amount'],
            ['key' => 'status', 'label' => 'Status'],
        ];
    }

    public function export(): StreamedResponse
    {
        set_time_limit(600);

        $filename = 'transaction_report_'.$this->startdate.'_to_'.$this->enddate.'_'.date('Y-m-d_H-i-s').'.csv';

        // Get transactions using repository
        $transactions = $this->banktransactionrepo->gettransactionbydaterange(
            $this->startdate,
            $this->enddate,
            $this->bankaccount
        ) ?? collect();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Write CSV headers
            fputcsv($handle, [
                'Date',
                'Customer',
                'Account Number',
                'Description',
                'Source Reference',
                'Statement Reference',
                'Reference Number',
                'Currency',
                'Amount',
                'Status',
            ]);

            foreach ($transactions as $transaction) {
                // Format date
                $formattedDate = $transaction->transactiondate;
                if (str_contains($transaction->transactiondate, '/')) {
                    try {
                        $formattedDate = Carbon::createFromFormat('d/m/Y', $transaction->transactiondate)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Keep original if parsing fails
                    }
                }

                fputcsv($handle, [
                    $formattedDate,
                    $transaction->customer?->name ?? '-',
                    $transaction->accountnumber,
                    $transaction->description,
                    $transaction->sourcereference,
                    $transaction->statementreference,
                    $transaction->referencenumber,
                    $transaction->currency,
                    $transaction->amount,
                    $transaction->status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function render()
    {
        $transactions = $this->getTransactions();

        return view('livewire.admin.finance.transactionreport', [
            'transactions' => $transactions,
            'bankaccounts' => $this->getBankAccounts(),
            'headers' => $this->headers(),
        ]);
    }
}

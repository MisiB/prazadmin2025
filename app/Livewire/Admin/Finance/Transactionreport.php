<?php

namespace App\Livewire\Admin\Finance;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ibanktransactionInterface;
use Carbon\Carbon;
use Livewire\Component;

class Transactionreport extends Component
{
    public $startdate = null;

    public $enddate = null;

    public $bankaccount = null;

    public $modal = false;

    protected $bankaccountrepo;

    protected $banktransactionrepo;

    public $transactions;

    public function boot(ibankaccountInterface $bankaccountrepo, ibanktransactionInterface $banktransactionrepo)
    {
        $this->bankaccountrepo = $bankaccountrepo;
        $this->banktransactionrepo = $banktransactionrepo;
    }

    public function mount()
    {
        $this->startdate = Carbon::now()->addDays(-7)->format('Y-m-d');
        $this->enddate = Carbon::now()->format('Y-m-d');
        $this->loadTransactions();
    }

    protected function loadTransactions(): void
    {
        try {
            $this->transactions = $this->banktransactionrepo->gettransactionbydaterange($this->startdate, $this->enddate, $this->bankaccount) ?? collect();
        } catch (\Exception $e) {
            $this->transactions = collect();
            session()->flash('error', 'Failed to load transactions: '.$e->getMessage());
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

        try {
            $this->transactions = $this->banktransactionrepo->gettransactionbydaterange(
                Carbon::parse($this->startdate)->format('Y-m-d'),
                Carbon::parse($this->enddate)->format('Y-m-d'),
                $this->bankaccount
            ) ?? collect();

            $this->modal = false;

            if ($this->transactions->isEmpty()) {
                session()->flash('info', 'No transactions found for the selected date range.');
            }
        } catch (\Exception $e) {
            $this->transactions = collect();
            session()->flash('error', 'Failed to retrieve transactions: '.$e->getMessage());
        }
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

    public function export()
    {
        if ($this->transactions->isEmpty()) {
            session()->flash('error', 'No transactions to export.');

            return;
        }

        $filename = 'transactions_'.$this->startdate.'_to_'.$this->enddate.'_'.date('Y-m-d_H-i-s').'.csv';
        $filePath = public_path($filename);

        $file = fopen($filePath, 'w');

        // Write headers
        fputcsv($file, [
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

        // Write data rows
        foreach ($this->transactions as $transaction) {
            // Format date
            $formattedDate = $transaction->transactiondate;
            if (str_contains($transaction->transactiondate, '/')) {
                $formattedDate = Carbon::createFromFormat('d/m/Y', $transaction->transactiondate)->format('Y-m-d');
            }

            fputcsv($file, [
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

        fclose($file);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.admin.finance.transactionreport', [
            'bankaccounts' => $this->getBankAccounts(),
            'headers' => $this->headers(),
        ]);
    }
}

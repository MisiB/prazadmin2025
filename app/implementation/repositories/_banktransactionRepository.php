<?php

namespace App\implementation\repositories;

use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ibankInterface;
use App\Interfaces\repositories\ibanktransactionInterface;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\isuspenseInterface;
use App\Interfaces\repositories\iwallettopupInterface;
use App\Models\Bankreconciliation;
use App\Models\Bankreconciliationdata;
use App\Models\Banktransaction;

class _banktransactionRepository implements ibanktransactionInterface
{
    /**
     * Create a new class instance.
     */
    protected $model;

    protected $bankrepo;

    protected $customerrepo;

    protected $suspenserepo;

    protected $bankaccountrepo;

    protected $wallettopuprepo;

    protected $bankreconciliationmodel;

    protected $bankreconciliationdatamodel;

    public function __construct(Banktransaction $model, ibankInterface $bank, icustomerInterface $customer, isuspenseInterface $suspense, ibankaccountInterface $bankaccount, iwallettopupInterface $wallettopuprepo, Bankreconciliation $bankreconciliationmodel, Bankreconciliationdata $bankreconciliationdatamodel)
    {
        $this->model = $model;
        $this->bankrepo = $bank;
        $this->customerrepo = $customer;
        $this->suspenserepo = $suspense;
        $this->bankaccountrepo = $bankaccount;
        $this->wallettopuprepo = $wallettopuprepo;
        $this->bankreconciliationmodel = $bankreconciliationmodel;
        $this->bankreconciliationdatamodel = $bankreconciliationdatamodel;
    }

    public function createtransaction(array $data)
    {
        $bank = $this->bankrepo->getBankBySalt($data['authcode']);
        if ($bank == null) {
            return ['message' => 'Unauthorized to post transaction', 'status' => 401];
        }
        $customer = $this->customerrepo->getCustomerByRegnumber($data['description']);
        $customer_number = '';
        $status = 'PENDING';
        $customer_id = null;
        if ($customer != null) {
            $customer_id = $customer->id;
            $customer_number = $customer->regnumber;
            $stataus = 'CLAIMED';
        }
        $checktranscation = $this->model->where('sourcereference', '=', $data['sourcereference'])->first();
        if ($checktranscation != null) {
            return ['message' => 'Reference already exists', 'status' => 200];
        }
        $transaction = $this->model->create([
            'bank_id' => $bank->id,
            'referencenumber' => $data['referencenumber'],
            'sourcereference' => $data['source_reference'],
            'statementreference' => $data['statement_reference'],
            'description' => $data['description'],
            'accountnumber' => $data['accountnumber'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'regnumber' => $customer_number,
            'transactiondate' => $data['trans_date'],
            'customer_id' => $customer_id,
            'status' => $status,
            'copied' => 0,
        ]);
        if ($customer != null) {
            $bankaccount = $this->bankaccountrepo->getBankAccountByBankIdAndAccountNumber($bank->id, $data['accountnumber']);
            if ($bankaccount != null) {
                $this->suspenserepo->create([
                    'customer_id' => $customer_id,
                    'sourcetype' => 'banktransaction',
                    'source_id' => $transaction->id,
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'status' => 'PENDING',
                    'accountnumber' => $data['accountnumber'],
                    'type' => $bankaccount->account_type,
                    'posted' => 0,
                ]);
            }
        }

        return ['message' => 'Reference number saved', 'status' => 'SUCCESS'];
    }

    public function recallpayment($refencenumber)
    {
        $transaction = $this->model->where('referencenumber', '=', $refencenumber)->first();
        if ($transaction == null) {
            return ['message' => 'Transaction not found', 'status' => 'ERROR'];
        }
        if ($transaction->status == 'PENDING') {
            $transaction->status = 'RECALLED';
            $transaction->save();

            return ['message' => 'Transaction recalled', 'status' => 'SUCCESS'];
        } else {
            return ['message' => 'Transaction already claimed cannot be reversed', 'status' => 'ERROR'];
        }
    }

    public function internalsearch($needle)
    {
        $transactions = $this->model->with('customer', 'bank', 'bankaccount')->where('statementreference', 'like', '%'.$needle.'%')
            ->orWhere('sourcereference', 'like', '%'.$needle.'%')
            ->orWhere('description', 'like', '%'.$needle.'%')
            ->get();

        return $transactions;
    }

    public function search($needle)
    {
        $transactions = $this->model->where('statementreference', 'like', '%'.$needle.'%')
            ->orWhere('sourcereference', 'like', '%'.$needle.'%')
            ->orWhere('description', 'like', '%'.$needle.'%')
            ->get();
        $array = [];
        if ($transactions->count() > 0) {
            foreach ($transactions as $transaction) {
                $date = str_replace('/', '-', $transaction->transactiondate);
                $newDate = date('Y-m-d', strtotime($date));
                $array[] = Collect([
                    'id' => $transaction->id,
                    'referencenumber' => $transaction->referencenumber,
                    'accountnumber' => $transaction->accountnumber,
                    'regnumber' => $transaction->regnumber,
                    'invoicenumber' => null,
                    'invoiceId' => null,
                    'bankId' => $transaction->bank_id,
                    'clientId' => null,
                    'accountId' => $transaction->customer_id,
                    'service' => null,
                    'description' => $transaction->description,
                    'transactionDate' => $newDate,
                    'statementReference' => $transaction->statementreference,
                    'sourceReference' => $transaction->sourcereference,
                    'currency' => $transaction->currency,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'account' => null,
                    'client' => null,
                    'bankaccount' => null,
                    'invoice' => null,
                    'banktransactionconv' => null,
                    'dateCreated' => $transaction->created_at,
                    'dateUpdated' => $transaction->updated_at,
                    'dateDeleted' => null,
                ]);
            }
        }

        return $array;
    }

    public function claim(array $data)
    {
        $transaction = $this->model->where('sourcereference', '=', $data['sourcereference'])->first();
        if ($transaction == null) {
            return ['message' => 'Bank transaction not found', 'status' => 'ERROR'];
        }
        if ($transaction->status == 'CLAIMED') {
            return ['message' => 'Bank transaction already claimed', 'status' => 'ERROR'];
        }

        $bankaccount = $this->bankaccountrepo->getBankAccountByBankIdAndAccountNumber($transaction->bank_id, $transaction->accountnumber);
        if ($bankaccount == null) {
            return ['message' => 'Bank account not found', 'status' => 'ERROR'];
        }
        $customer = $this->customerrepo->getCustomerByRegnumber($data['regnumber']);
        if ($customer == null) {
            return ['message' => 'Regnumber not found', 'status' => 'ERROR'];
        }
        $suspenresponse = $this->suspenserepo->create([
            'customer_id' => $customer->id,
            'sourcetype' => 'banktransaction',
            'source_id' => $transaction->id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'status' => 'PENDING',
            'accountnumber' => $transaction->accountnumber,
            'type' => $bankaccount->account_type,
            'posted' => 0,
        ]);
        $transaction->customer_id = $customer->id;
        $transaction->status = 'CLAIMED';
        $transaction->save();

        return ['message' => 'Transaction claimed', 'status' => 'SUCCESS'];
    }

    public function link(array $data)
    {
        $transaction = $this->model->where('sourcereference', '=', $data['sourcereference'])->first();
        if ($transaction == null) {
            return ['message' => 'Bank transaction not found', 'status' => 'ERROR'];
        }
        if ($transaction->status == 'CLAIMED') {
            return ['message' => 'Bank transaction already claimed', 'status' => 'ERROR'];
        }

        $bankaccount = $this->bankaccountrepo->getBankAccountByBankIdAndAccountNumber($transaction->bank_id, $transaction->accountnumber);
        if ($bankaccount == null) {
            return ['message' => 'Bank account not found', 'status' => 'ERROR'];
        }
        $customer = $this->customerrepo->getCustomerByRegnumber($data['regnumber']);
        if ($customer == null) {
            return ['message' => 'Regnumber not found', 'status' => 'ERROR'];
        }
        $transaction->customer_id = $customer->id;
        $transaction->status = 'CLAIMED';
        $transaction->save();
        $response = $this->wallettopuprepo->linkwallet(['id' => $data['wallettopup_id'], 'banktransaction_id' => $transaction->id]);
        if ($response['status'] == 'ERROR') {
            return ['message' => $response['message'], 'status' => 'ERROR'];
        } else {
            return ['message' => $response['message'], 'status' => 'SUCCESS'];
        }
    }

    public function block($id, $status)
    {
        $transaction = $this->model->find($id);
        if ($transaction == null) {
            return ['message' => 'Bank transaction not found', 'status' => 'ERROR'];
        }
        $transaction->status = $status;
        $transaction->save();

        return ['message' => 'Transaction '.$status, 'status' => 'SUCCESS'];
    }

    public function getlatesttransactions()
    {
        return $this->model->whereDate('created_at', '>=', now())->orderBy('created_at', 'desc')->get();
    }

    public function gettransaction($id)
    {
        return $this->model->with('customer', 'bank', 'bankaccount', 'suspense.suspenseutilizations.invoice.inventoryitem')->find($id);
    }

    public function gettransactionbydaterange($startdate, $enddate, $bankaccount = null)
    {
        return $this->model->whereBetween('transactiondate', [$startdate, $enddate])->when($bankaccount != null, function ($query) use ($bankaccount) {
            return $query->where('accountnumber', $bankaccount);
        })->orderBy('created_at', 'desc')->get();
    }

    public function getbankreconciliations($year)
    {
        return $this->bankreconciliationmodel->with('currency', 'bankaccount', 'user')->where('year', '=', $year)->get();
    }

    public function getbankreconciliation($id)
    {
        return $this->bankreconciliationmodel->find($id);
    }

    public function extractdata($id)
    {
        $bankreconciliation = $this->bankreconciliationmodel->find($id);
        if ($bankreconciliation == null) {
            return ['message' => 'Bank reconciliation not found', 'status' => 'ERROR'];
        }
        try {
            $path = storage_path('app/private/'.$bankreconciliation->filename);

            if (! file_exists($path)) {
                return ['message' => 'File not found', 'status' => 'ERROR'];
            }
            $file = fopen($path, 'r');
            $i = 0;
            while (($row = fgetcsv($file, null, ',')) != false) {
                if ($i > 0) {
                    $date = $row[0];
                    $description = $row[1];
                    $refencenumber = $row[2];
                    $currency = $row[3];
                    $amount = $row[4];
                    $type = $row[5];
                    $balance = $row[7];
                    $this->bankreconciliationdatamodel->create([
                        'bankreconciliation_id' => $bankreconciliation->id,
                        'tnxdate' => $date,
                        'tnxdescription' => $description,
                        'tnxreference' => $refencenumber,
                        'tnxamount' => $amount,
                        'tnxtype' => $type,
                        'balance' => $balance,
                    ]);
                }
                $i++;
            }
            $bankreconciliation->status = 'EXTRACTED';
            $bankreconciliation->save();

            return ['message' => 'Data extracted', 'status' => 'SUCCESS'];
        } catch (\Exception $e) {
            return ['message' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }

    public function createbankreconciliation(array $data)
    {
        try {
            $this->bankreconciliationmodel->create($data);

            return ['message' => 'Bank reconciliation created', 'status' => 'SUCCESS'];
        } catch (\Exception $e) {
            return ['message' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }

    public function updatebankreconciliation($id, array $data)
    {
        try {
            $bankreconciliation = $this->bankreconciliationmodel->find($id);
            if ($bankreconciliation == null) {
                return ['message' => 'Bank reconciliation not found', 'status' => 'ERROR'];
            }
            if ($data['filename'] == null) {
                unset($data['filename']);
            }
            $bankreconciliation->update($data);

            return ['message' => 'Bank reconciliation updated', 'status' => 'SUCCESS'];
        } catch (\Exception $e) {
            return ['message' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }

    public function deletebankreconciliation($id)
    {
        try {
            $bankreconciliation = $this->bankreconciliationmodel->find($id);
            if ($bankreconciliation == null) {
                return ['message' => 'Bank reconciliation not found', 'status' => 'ERROR'];
            }
            $bankreconciliation->delete();

            return ['message' => 'Bank reconciliation deleted', 'status' => 'SUCCESS'];
        } catch (\Exception $e) {
            return ['message' => $e->getMessage(), 'status' => 'ERROR'];
        }
    }

    public function syncdata($id)
    {
        $data = $this->bankreconciliationmodel->with('bankreconciliationdata')->find($id);
        foreach ($data->bankreconciliationdata as $d) {
            $banktransaction = $this->model->where('sourcereference', '=', $d->tnxreference)->first();
            if ($banktransaction == null) {
                $d->status = 'NOT FOUND';
                $d->save();

                continue;
            }
            $d->banktransaction_id = $banktransaction->id;
            $d->status = 'SYNCED';
            $d->save();
        }
        $data->status = 'SYNCED';
        $data->save();

        return ['message' => 'Data synced', 'status' => 'SUCCESS'];
    }

    public function viewreport($id, $filterbystatus, $showdebit)
    {

        $data = $this->bankreconciliationmodel->with('bankreconciliationdata.banktransaction.customer', 'bankreconciliationdata.banktransaction.suspense.suspenseutilizations.invoice.inventoryitem')->find($id);
        if ($filterbystatus != 'ALL') {
            //  $data->bankreconciliationdata = $data->bankreconciliationdata->where("status","=",$filterbystatus);
        }
        if ($showdebit) {
            //  $data->bankreconciliationdata = $data->bankreconciliationdata->whereIn("tnxtype",["Dr","Cr"]);
        } else {
            //  $data->bankreconciliationdata = $data->bankreconciliationdata->where("tnxtype","=","Cr");
        }

        return $data;
    }

    public function gettransactions($customer_id)
    {
        return $this->model->where('customer_id', '=', "$customer_id")->get();
    }
}

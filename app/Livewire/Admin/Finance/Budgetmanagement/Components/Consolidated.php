<?php

namespace App\Livewire\Admin\Finance\Budgetmanagement\Components;

use App\Imports\BudgetItemsImport;
use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\idepartmentInterface;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Mary\Traits\Toast;

class Consolidated extends Component
{
    use Toast;
    use WithFileUploads;

    public $budget;

    public $department_id;

    protected $departmentrepo;

    protected $budgetrepo;

    public $budgetitems;

    public $totalbudget = 0;

    public $totalutilized = 0;

    public $totalremaining = 0;

    public $importModal = false;

    public $importFile;

    public $importErrors = [];

    public function boot(idepartmentInterface $departmentrepo, ibudgetInterface $budgetrepo): void
    {
        $this->departmentrepo = $departmentrepo;
        $this->budgetrepo = $budgetrepo;
    }

    public function departments()
    {
        return $this->departmentrepo->getdepartments();
    }

    public function mount($budget): void
    {
        $this->budget = $budget;
        // Show all budget items by default (not filtered by status)
        $this->budgetitems = $this->budget->budgetitems;
    }

    public function headers(): array
    {
        return [
            ['key' => 'activity', 'label' => 'Activity'],
            ['key' => 'strategysubprogrammeoutput.output', 'label' => 'Output'],
            ['key' => 'department.name', 'label' => 'Department'],
            ['key' => 'expensecategory.name', 'label' => 'Expense Category'],
            ['key' => 'sourceoffund.name', 'label' => 'Source of Funds'],
            ['key' => 'quantity', 'label' => 'Quantity'],
            ['key' => 'unitprice', 'label' => 'Unit Price'],
            ['key' => 'total', 'label' => 'Total'],
            ['key' => 'utilized', 'label' => 'Utilized'],
            ['key' => 'remaining', 'label' => 'Remaining'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'action', 'label' => ''],
        ];
    }

    public function updatedDepartmentId(): void
    {
        $this->budgetitems = $this->budget->budgetitems->where('department_id', $this->department_id);
    }

    public function computetotals(): void
    {
        $budgetitems = $this->budgetitems;
        $this->totalbudget = $budgetitems->sum('total');
        $this->totalutilized = 0;
        $this->totalremaining = $this->totalbudget - $this->totalutilized;
    }

    public function openImportModal(): void
    {
        $this->importModal = true;
        $this->importFile = null;
        $this->importErrors = [];
    }

    public function closeImportModal(): void
    {
        $this->importModal = false;
        $this->importFile = null;
        $this->importErrors = [];
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new \App\Exports\BudgetImportTemplateExport, 'budget_import_template.xlsx');
    }

    public function importBudget(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $currencyId = $this->budget->currency_id ?? 1;

            $import = new BudgetItemsImport($this->budget->id, $currencyId);
            Excel::import($import, $this->importFile->getRealPath());

            $importedItems = $import->getImportedItems();
            $parseErrors = $import->getErrors();

            if (empty($importedItems)) {
                $this->importErrors = array_merge(['No valid items found in the file.'], $parseErrors);
                $this->error('Import failed. Please check the errors below.');

                return;
            }

            $result = $this->budgetrepo->importbudgetitems($this->budget->id, $importedItems);

            if ($result['status'] === 'success') {
                $this->success($result['message']);
                $this->closeImportModal();
                $this->dispatch('$refresh');
            } elseif ($result['status'] === 'partial') {
                $this->importErrors = array_merge($parseErrors, $result['errors']);
                $this->warning($result['message']);
            } else {
                $this->importErrors = array_merge($parseErrors, $result['errors']);
                $this->error($result['message']);
            }
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            foreach ($failures as $failure) {
                $this->importErrors[] = "Row {$failure->row()}: ".$failure->attribute().' - '.implode(', ', $failure->errors());
            }
            $this->error('Validation errors found. Please check the errors below.');
        } catch (\Exception $e) {
            $this->importErrors[] = $e->getMessage();
            $this->error('An error occurred during import.');
        }
    }

    public function render()
    {
        return view('livewire.admin.finance.budgetmanagement.components.consolidated', [
            'headers' => $this->headers(),
            'departments' => $this->departments(),
            'budgetitems' => $this->budgetitems,
            'summary' => $this->computetotals(),
        ]);
    }
}

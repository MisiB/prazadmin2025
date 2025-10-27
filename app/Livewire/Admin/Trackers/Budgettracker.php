<?php

namespace App\Livewire\Admin\Trackers;

use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\idepartmentInterface;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Budgettracker extends Component
{
    public $breadcrumbs = [];

    public $budgets = [];

    public $currentBudgetId;

    protected $budgetRepository;

    protected $departmentRepository;

    public function boot(ibudgetInterface $budgetRepository, idepartmentInterface $departmentRepository)
    {
        $this->budgetRepository = $budgetRepository;
        $this->departmentRepository = $departmentRepository;
    }

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('admin.home')],
            ['label' => 'Budget Tracker'],
        ];

        $this->budgets = $this->budgetRepository->getbudgets();

        if ($this->budgets->isNotEmpty()) {
            $this->currentBudgetId = $this->budgets->first()->id;
        }
    }

    public function updatedCurrentBudgetId()
    {
        // Refresh data when budget selection changes
    }

    public function getBudgetSummary(): array
    {
        if (! $this->currentBudgetId) {
            return [
                'total_budget' => 0,
                'total_spent' => 0,
                'total_remaining' => 0,
                'percentage_spent' => 0,
                'total_departments' => 0,
            ];
        }

        $budget = $this->budgetRepository->getbudget($this->currentBudgetId);

        $totalBudget = $budget->budgetitems()->sum('total');
        $totalSpent = $this->getTotalSpent($this->currentBudgetId);
        $totalRemaining = $totalBudget - $totalSpent;
        $percentageSpent = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        return [
            'total_budget' => $totalBudget,
            'total_spent' => $totalSpent,
            'total_remaining' => $totalRemaining,
            'percentage_spent' => $percentageSpent,
            'total_departments' => $budget->budgetitems()->distinct('department_id')->count('department_id'),
        ];
    }

    public function getTotalSpent($budgetId): float
    {
        // Calculate total spent from purchase requisition awards linked to budget items
        return DB::table('budgetitems')
            ->join('purchaserequisitions', 'budgetitems.id', '=', 'purchaserequisitions.budgetitem_id')
            ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
            ->where('budgetitems.budget_id', $budgetId)
            ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
            ->sum('purchaserequisitionawards.amount');
    }

    public function getDepartmentBudgets()
    {
        if (! $this->currentBudgetId) {
            return collect();
        }

        $departments = collect();

        $budgetItems = $this->budgetRepository->getbudgetitems($this->currentBudgetId);

        $groupedByDepartment = $budgetItems->groupBy('department_id');

        foreach ($groupedByDepartment as $departmentId => $items) {
            $department = $items->first()->department;

            if (! $department) {
                continue;
            }

            $allocated = $items->sum('total');
            $spent = $this->getDepartmentSpent($items->pluck('id'));
            $remaining = $allocated - $spent;
            $percentage = $allocated > 0 ? round(($spent / $allocated) * 100, 1) : 0;

            $status = $this->getBudgetStatus($percentage);

            $departments->push([
                'id' => $department->id,
                'name' => $department->name,
                'allocated' => $allocated,
                'spent' => $spent,
                'remaining' => $remaining,
                'percentage' => $percentage,
                'status' => $status,
                'items_count' => $items->count(),
            ]);
        }

        return $departments->sortByDesc('allocated');
    }

    public function getDepartmentSpent($budgetItemIds): float
    {
        return DB::table('purchaserequisitions')
            ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
            ->whereIn('purchaserequisitions.budgetitem_id', $budgetItemIds)
            ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
            ->sum('purchaserequisitionawards.amount');
    }

    public function getBudgetStatus(float $percentage): array
    {
        if ($percentage >= 90) {
            return ['label' => 'At Risk', 'color' => 'red'];
        }

        if ($percentage >= 75) {
            return ['label' => 'Warning', 'color' => 'yellow'];
        }

        return ['label' => 'On Track', 'color' => 'green'];
    }

    public function getBudgetItemSpent($budgetItemId): float
    {
        return DB::table('purchaserequisitions')
            ->join('purchaserequisitionawards', 'purchaserequisitions.id', '=', 'purchaserequisitionawards.purchaserequisition_id')
            ->where('purchaserequisitions.budgetitem_id', $budgetItemId)
            ->whereIn('purchaserequisitionawards.status', ['approved', 'completed'])
            ->sum('purchaserequisitionawards.amount');
    }

    public function getBudgetItemUtilization($budgetItem): array
    {
        $allocated = $budgetItem->total ?? 0;
        $spent = $this->getBudgetItemSpent($budgetItem->id);
        $remaining = $allocated - $spent;
        $percentage = $allocated > 0 ? round(($spent / $allocated) * 100, 1) : 0;

        return [
            'spent' => $spent,
            'remaining' => $remaining,
            'percentage' => $percentage,
            'status' => $this->getBudgetStatus($percentage),
        ];
    }

    public function render()
    {
        return view('livewire.admin.trackers.budgettracker', [
            'budgetSummary' => $this->getBudgetSummary(),
            'departmentBudgets' => $this->getDepartmentBudgets(),
        ]);
    }
}

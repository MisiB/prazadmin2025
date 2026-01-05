<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Expensecategory;
use App\Models\Sourceoffund;
use App\Models\Strategyprogramme;
use App\Models\Strategysubprogrammeoutput;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BudgetItemsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected int $budgetId;

    protected int $currencyId;

    protected array $importedItems = [];

    protected array $errors = [];

    /**
     * Map of alternative column names to expected names
     */
    protected array $columnAliases = [
        // activity
        'activity' => 'activity',
        'activity_name' => 'activity',
        'item' => 'activity',
        'budget_item' => 'activity',
        'budgetitem' => 'activity',
        // description
        'description' => 'description',
        'desc' => 'description',
        'details' => 'description',
        // department
        'department' => 'department',
        'dept' => 'department',
        'department_name' => 'department',
        // expense_category
        'expense_category' => 'expense_category',
        'expensecategory' => 'expense_category',
        'expense' => 'expense_category',
        'category' => 'expense_category',
        // programme_title
        'programme_title' => 'programme_title',
        'programmetitle' => 'programme_title',
        'programme' => 'programme_title',
        'program' => 'programme_title',
        'program_title' => 'programme_title',
        // output
        'output' => 'output',
        'outputs' => 'output',
        'strategy_output' => 'output',
        // source_of_fund
        'source_of_fund' => 'source_of_fund',
        'sourceoffund' => 'source_of_fund',
        'source' => 'source_of_fund',
        'fund_source' => 'source_of_fund',
        'funding_source' => 'source_of_fund',
        'fundsource' => 'source_of_fund',
        // quantity
        'quantity' => 'quantity',
        'qty' => 'quantity',
        'units' => 'quantity',
        // unit_price
        'unit_price' => 'unit_price',
        'unitprice' => 'unit_price',
        'price' => 'unit_price',
        'rate' => 'unit_price',
        'unit_cost' => 'unit_price',
        'unitcost' => 'unit_price',
        // focus_date
        'focus_date' => 'focus_date',
        'focusdate' => 'focus_date',
        'date' => 'focus_date',
        'target_date' => 'focus_date',
    ];

    public function __construct(int $budgetId, int $currencyId)
    {
        $this->budgetId = $budgetId;
        $this->currencyId = $currencyId;
    }

    /**
     * Clean a column key to a standard format
     */
    protected function cleanKey(string $key): string
    {
        // Trim whitespace
        $key = trim($key);
        // Convert to lowercase
        $key = strtolower($key);
        // Replace spaces, hyphens, dots with underscores
        $key = preg_replace('/[\s\-\.]+/', '_', $key);
        // Remove any non-alphanumeric characters except underscores
        $key = preg_replace('/[^a-z0-9_]/', '', $key);
        // Remove multiple consecutive underscores
        $key = preg_replace('/_+/', '_', $key);
        // Trim underscores from start/end
        $key = trim($key, '_');

        return $key;
    }

    /**
     * Normalize a row by mapping alternative column names to expected names
     */
    protected function normalizeRow(Collection $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            // Clean the key
            $cleanKey = $this->cleanKey((string) $key);

            // Check if this key has an alias
            $mappedKey = $this->columnAliases[$cleanKey] ?? $cleanKey;

            $normalized[$mappedKey] = $value;
        }

        return $normalized;
    }

    /**
     * Sanitize numeric values that might be stored as text in Excel
     */
    private function sanitizeNumeric($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove any non-numeric characters except decimal point and minus
        $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return (float) ($cleaned ?: 0);
    }

    /**
     * Parse Excel date which can be a serial number or string
     */
    private function parseExcelDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Check if it's an Excel serial date (numeric)
            if (is_numeric($value)) {
                // Excel serial date - convert to Carbon
                // Excel dates are days since 1900-01-01 (with a bug where 1900 is considered a leap year)
                $unixTimestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value);

                return \Carbon\Carbon::createFromTimestamp($unixTimestamp)->format('Y-m-d');
            }

            // Try parsing as a regular date string
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function prepareForValidation(array $data, int $index): array
    {
        // Normalize column names using aliases
        $normalized = [];
        foreach ($data as $key => $value) {
            $cleanKey = $this->cleanKey((string) $key);
            $mappedKey = $this->columnAliases[$cleanKey] ?? $cleanKey;
            $normalized[$mappedKey] = $value;
        }

        // Sanitize quantity and unit_price fields
        if (isset($normalized['quantity'])) {
            $normalized['quantity'] = $this->sanitizeNumeric($normalized['quantity']);
        }

        if (isset($normalized['unit_price'])) {
            $normalized['unit_price'] = $this->sanitizeNumeric($normalized['unit_price']);
        }

        return $normalized;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because row 1 is header

            // Normalize column names
            $data = $this->normalizeRow($row);

            try {
                // Skip empty rows
                if (empty($data['activity']) && empty($data['department'])) {
                    continue;
                }

                $department = Department::where('name', trim($data['department'] ?? ''))->first();
                if (! $department) {
                    $this->errors[] = "Row {$rowNumber}: Department '{$data['department']}' not found";

                    continue;
                }

                // Auto-create expense category if it doesn't exist
                $expenseCategory = Expensecategory::firstOrCreate(
                    ['name' => trim($data['expense_category'] ?? '')]
                );

                // Auto-create source of fund if it doesn't exist
                $sourceOfFund = Sourceoffund::firstOrCreate(
                    ['name' => trim($data['source_of_fund'] ?? '')]
                );

                // Programme is optional - look up if provided, set to null if not found (no auto-create)
                $strategyprogramme = null;
                if (! empty($data['programme_title'])) {
                    $strategyprogramme = Strategyprogramme::where('title', 'like', '%'.trim($data['programme_title']).'%')->first();
                }

                // Output is optional - try to find it if provided
                $output = null;
                if (! empty($data['output'])) {
                    $output = Strategysubprogrammeoutput::where('output', 'like', '%'.trim($data['output']).'%')->first();
                }

                $quantity = (int) ($data['quantity'] ?? 0);
                $unitPrice = round((float) ($data['unit_price'] ?? 0), 2);
                $total = round($quantity * $unitPrice, 2);

                $focusDate = $this->parseExcelDate($data['focus_date'] ?? null);

                $this->importedItems[] = [
                    'budget_id' => $this->budgetId,
                    'department_id' => $department->id,
                    'activity' => trim($data['activity'] ?? ''),
                    'description' => trim($data['description'] ?? ''),
                    'expensecategory_id' => $expenseCategory->id,
                    'strategyprogramme_id' => $strategyprogramme?->id,
                    'strategysubprogrammeoutput_id' => $output?->id,
                    'sourceoffund_id' => $sourceOfFund->id,
                    'quantity' => $quantity,
                    'unitprice' => $unitPrice,
                    'total' => $total,
                    'currency_id' => $this->currencyId,
                    'focusdate' => $focusDate,
                    'status' => 'PENDING',
                ];
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: ".$e->getMessage();
            }
        }
    }

    public function rules(): array
    {
        return [
            'activity' => 'required|string|max:255',
            'department' => 'required|string',
            'expense_category' => 'required|string',
            'programme_title' => 'nullable|string',
            'output' => 'nullable|string',
            'source_of_fund' => 'required|string',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'activity.required' => 'Activity is required',
            'department.required' => 'Department is required',
            'expense_category.required' => 'Expense category is required',
            'source_of_fund.required' => 'Source of fund is required',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be at least 1',
            'unit_price.required' => 'Unit price is required',
            'unit_price.min' => 'Unit price must be a positive number',
        ];
    }

    public function getImportedItems(): array
    {
        return $this->importedItems;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

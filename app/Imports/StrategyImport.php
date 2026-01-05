<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Departmentoutput;
use App\Models\Indicator;
use App\Models\Outcome;
use App\Models\Output;
use App\Models\Programme;
use App\Models\Target;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StrategyImport implements ToCollection, WithHeadingRow
{
    protected int $strategyId;

    protected string $userId;

    protected array $errors = [];

    protected int $importedCount = 0;

    protected array $programmeCache = [];

    protected array $outcomeCache = [];

    protected array $outputCache = [];

    protected array $departmentoutputCache = [];

    protected array $indicatorCache = [];

    public function __construct(int $strategyId, string $userId)
    {
        $this->strategyId = $strategyId;
        $this->userId = $userId;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            // Skip empty rows
            $programmeCode = trim($row['programme_code'] ?? '');
            if (empty($programmeCode)) {
                continue;
            }

            try {
                // 1. Find or create Programme
                $programmeTitle = trim($row['programme_title'] ?? '');

                $programmeKey = $this->strategyId.'|'.$programmeCode;
                if (! isset($this->programmeCache[$programmeKey])) {
                    $programme = Programme::firstOrCreate(
                        [
                            'strategy_id' => $this->strategyId,
                            'code' => $programmeCode,
                        ],
                        [
                            'title' => $programmeTitle,
                            'createdby' => $this->userId,
                            'status' => 'PENDING',
                        ]
                    );
                    $this->programmeCache[$programmeKey] = $programme->id;
                }
                $programmeId = $this->programmeCache[$programmeKey];

                // 2. Find or create Outcome
                $outcomeTitle = trim($row['outcome_title'] ?? '');
                if (empty($outcomeTitle)) {
                    $this->errors[] = "Row {$rowNumber}: Outcome title is required";

                    continue;
                }

                $outcomeKey = $programmeId.'|'.$outcomeTitle;
                if (! isset($this->outcomeCache[$outcomeKey])) {
                    $outcome = Outcome::firstOrCreate(
                        [
                            'programme_id' => $programmeId,
                            'title' => $outcomeTitle,
                        ],
                        [
                            'createdby' => $this->userId,
                            'status' => 'PENDING',
                        ]
                    );
                    $this->outcomeCache[$outcomeKey] = $outcome->id;
                }
                $outcomeId = $this->outcomeCache[$outcomeKey];

                // 3. Find or create Output
                $outputTitle = trim($row['output_title'] ?? '');
                if (empty($outputTitle)) {
                    $this->errors[] = "Row {$rowNumber}: Output title is required";

                    continue;
                }

                $outputKey = $outcomeId.'|'.$outputTitle;
                if (! isset($this->outputCache[$outputKey])) {
                    $output = Output::firstOrCreate(
                        [
                            'outcome_id' => $outcomeId,
                            'title' => $outputTitle,
                        ],
                        [
                            'createdby' => $this->userId,
                            'status' => 'PENDING',
                        ]
                    );
                    $this->outputCache[$outputKey] = $output->id;
                }
                $outputId = $this->outputCache[$outputKey];

                // 4. Find Department and create Departmentoutput (Sub-programme)
                $departmentName = trim($row['department'] ?? '');
                if (empty($departmentName)) {
                    $this->errors[] = "Row {$rowNumber}: Department is required";

                    continue;
                }

                $department = Department::where('name', $departmentName)->first();
                if (! $department) {
                    $this->errors[] = "Row {$rowNumber}: Department '{$departmentName}' not found";

                    continue;
                }

                $departmentoutputKey = $outputId.'|'.$department->id;
                if (! isset($this->departmentoutputCache[$departmentoutputKey])) {
                    $departmentoutput = Departmentoutput::firstOrCreate(
                        [
                            'output_id' => $outputId,
                            'department_id' => $department->id,
                        ],
                        [
                            'weightage' => (int) ($row['weightage'] ?? 100),
                            'createdby' => $this->userId,
                            'status' => 'PENDING',
                        ]
                    );
                    $this->departmentoutputCache[$departmentoutputKey] = $departmentoutput->id;
                }
                $departmentoutputId = $this->departmentoutputCache[$departmentoutputKey];

                // 5. Find or create Indicator (if provided)
                $indicatorTitle = trim($row['indicator_title'] ?? '');
                if (! empty($indicatorTitle)) {
                    $indicatorUom = trim($row['indicator_uom'] ?? 'Number');

                    $indicatorKey = $departmentoutputId.'|'.$indicatorTitle;
                    if (! isset($this->indicatorCache[$indicatorKey])) {
                        $indicator = Indicator::firstOrCreate(
                            [
                                'departmentoutput_id' => $departmentoutputId,
                                'title' => $indicatorTitle,
                            ],
                            [
                                'uom' => $indicatorUom,
                                'createdby' => $this->userId,
                                'status' => 'PENDING',
                            ]
                        );
                        $this->indicatorCache[$indicatorKey] = $indicator->id;
                    }
                    $indicatorId = $this->indicatorCache[$indicatorKey];

                    // 6. Create Target (if provided)
                    $targetYear = trim($row['target_year'] ?? '');
                    $targetValue = trim($row['target_value'] ?? '');

                    if (! empty($targetYear) && $targetValue !== '') {
                        Target::firstOrCreate(
                            [
                                'indicator_id' => $indicatorId,
                                'year' => (int) $targetYear,
                            ],
                            [
                                'target' => (int) $targetValue,
                                'variance' => (int) ($row['target_variance'] ?? 0),
                                'createdby' => $this->userId,
                                'status' => 'PENDING',
                            ]
                        );
                    }
                }

                $this->importedCount++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: ".$e->getMessage();
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}

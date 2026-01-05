<?php

namespace App\implementation\services;

use App\Exports\StrategyImportTemplateExport;
use App\Imports\StrategyImport;
use App\Interfaces\repositories\istrategyInterface;
use App\Interfaces\services\IStrategyImportService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class _strategyImportService implements IStrategyImportService
{
    public function __construct(
        protected istrategyInterface $strategyRepository
    ) {}

    public function importStrategy(int $strategyId, array $file): array
    {
        try {
            $strategy = $this->strategyRepository->getstrategy($strategyId);

            if (! $strategy) {
                return ['status' => 'error', 'message' => 'Strategy not found'];
            }

            if ($strategy->status !== 'Draft') {
                return ['status' => 'error', 'message' => 'Only draft strategies can be imported to'];
            }

            $import = new StrategyImport($strategyId, Auth::user()->id);
            Excel::import($import, $file['path']);

            $errors = $import->getErrors();
            $imported = $import->getImportedCount();

            if (count($errors) > 0) {
                return [
                    'status' => 'warning',
                    'message' => "Imported {$imported} rows with ".count($errors).' errors',
                    'errors' => $errors,
                ];
            }

            return ['status' => 'success', 'message' => "Successfully imported {$imported} rows"];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function downloadTemplate(int $strategyId): mixed
    {
        return Excel::download(new StrategyImportTemplateExport($strategyId), 'strategy_import_template.xlsx');
    }
}

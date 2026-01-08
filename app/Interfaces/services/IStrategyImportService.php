<?php

namespace App\Interfaces\services;

interface IStrategyImportService
{
    public function importStrategy(int $strategyId, array $file): array;

    public function downloadTemplate(int $strategyId): mixed;
}


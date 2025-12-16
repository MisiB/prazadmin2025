<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Dashboard Report - {{ $generatedAt }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
            .no-print {
                display: none;
            }
            body {
                background: white;
            }
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-white to-gray-50">
    <!-- Print Button -->
    <div class="print-button no-print">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg">
            📄 Print / Save as PDF
        </button>
    </div>

    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Organization Dashboard Report
                </h1>
                <div class="text-gray-600 space-y-1">
                    <p><strong>Generated:</strong> {{ $generatedAt }}</p>
                    <p><strong>Period:</strong> {{ $startDate }} to {{ $endDate }} | <strong>Filter:</strong> {{ ucfirst($filterType) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        
        {{-- Include all the dashboard sections --}}
        @include('livewire.admin.trackers.partials.dashboard-content', [
            'overallMetrics' => $overallMetrics,
            'organizationHealth' => $organizationHealth,
            'taskApprovalMetrics' => $taskApprovalMetrics,
            'supervisorApprovalRates' => $supervisorApprovalRates,
            'weeklyTopApprovers' => $weeklyTopApprovers,
            'departmentBreakdown' => $departmentBreakdown,
            'issueResolution' => $issueResolution,
            'riskIndicators' => $riskIndicators,
            'workplanProgress' => $workplanProgress,
            'productivityMetrics' => $productivityMetrics,
            'budgetDistribution' => $budgetDistribution,
            'topPerformers' => $topPerformers,
            'workloadDistribution' => $workloadDistribution,
        ])

    </div>

    <!-- Footer -->
    <div class="bg-white border-t border-gray-200 py-4 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500">
            <p>Report generated on {{ $generatedAt }} | Period: {{ $startDate }} to {{ $endDate }}</p>
        </div>
    </div>
</body>
</html>

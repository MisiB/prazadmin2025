<?php

namespace App\Http\Controllers;

use App\implementation\services\_publicworkshopService;
use App\Models\Workshop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class PublicWorkshopController extends Controller
{

    protected _publicworkshopService $publicWorkshopService;

    public function __construct(_publicworkshopService $publicWorkshopService)
    {
        $this->publicWorkshopService = $publicWorkshopService;
    }

    /**
     * Get published workshops
     */
    public function getPublishedWorkshops(): JsonResponse
    {
        $result = $this->publicWorkshopService->getPublishedWorkshops();

        if ($result['status'] === 'error') {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    /**
     * Get workshop details
     */
    public function getWorkshop($id): JsonResponse
    {
        $result = $this->publicWorkshopService->getWorkshopDetails($id);

        if ($result['status'] === 'error') {
            return response()->json($result, 404);
        }

        // Get additional data
        $orderStats = $this->publicWorkshopService->getOrderStats($id);
        $currencies = $this->publicWorkshopService->getCurrencies();

        return response()->json([
            'status' => 'success',
            'data' => [
                'workshop' => $result['data'],
                'order_stats' => $orderStats['data'] ?? null,
                'currencies' => $currencies,
                'document_url' => $this->publicWorkshopService->getDocumentUrl($result['data']->document_url)
            ]
        ]);
    }

    /**
     * Search for customer by registration number
     */
    public function searchCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'prnumber' => 'required|string'
        ]);

        $result = $this->publicWorkshopService->searchCustomer($request->prnumber);

        if ($result['status'] === 'error') {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * Get existing workshop order for customer
     */
    public function getWorkshopOrder($workshopId, $customerId): JsonResponse
    {
        $result = $this->publicWorkshopService->getWorkshopOrder($workshopId, $customerId);
        return response()->json($result);
    }

    /**
     * Create or update workshop order
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'workshop_id' => 'required|exists:workshops,id',
            'customer_id' => 'required|exists:customers,id',
            'delegates' => 'required|numeric|min:1',
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        $result = $this->publicWorkshopService->createOrUpdateOrder(
            $request->workshop_id,
            $request->customer_id,
            $request->only(['delegates', 'name', 'surname', 'phone', 'email'])
        );

        if ($result['status'] === 'error') {
            return response()->json($result, 400);
        }

        return response()->json($result, 201);
    }

    /**
     * Get exchange rates for currency
     */
    public function getExchangeRates(Request $request): JsonResponse
    {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            // 'workshop_created_at' => 'nullable|date'
        ]);

        $rates = $this->publicWorkshopService->getExchangeRates(
            $request->currency_id,
            // $request->workshop_created_at
        );

        return response()->json([
            'status' => 'success',
            'data' => $rates
        ]);
    }

    /**
     * Save payment for workshop order
     */
    public function savePayment(Request $request, $orderId): JsonResponse
    {
        $request->validate([
            'exchangerate' => 'required|exists:exchangerates,id',
            'amountdue' => 'required|numeric|min:0',
            'currency' => 'required|exists:currencies,id',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $result = $this->publicWorkshopService->savePayment(
            $orderId,
            $request->only(['exchangerate', 'amountdue', 'currency', 'document'])
        );

        if ($result['status'] === 'error') {
            return Response::json($result, 400);
        }


        return Response::json($result);
    }

    /**
     * Get delegates for an order
     */
    public function getDelegates($orderId): JsonResponse
    {
        $result = $this->publicWorkshopService->getDelegates($orderId);
        return response()->json($result);
    }

    /**
     * Create delegate
     */
    public function createDelegate(Request $request): JsonResponse
    {
        $request->validate([
            'workshop_id' => 'required|exists:workshops,id',
            'workshoporder_id' => 'required|exists:workshoporders,id',
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:100',
            'nationaL_id' => 'nullable|string|max:50',
            'gender' => 'nullable|string|max:10',
            'designation' => 'nullable|string|max:100'
        ]);

        $result = $this->publicWorkshopService->createDelegate($request->all());

        if ($result['status'] === 'error') {
            return response()->json($result, 400);
        }

        return response()->json($result, 201);
    }

    /**
     * Update delegate
     */
    public function updateDelegate(Request $request, $delegateId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:50',
            'gender' => 'nullable|string|max:10',
            'designation' => 'nullable|string|max:100'
        ]);

        $result = $this->publicWorkshopService->updateDelegate(
            $delegateId,
            $request->only(['name', 'surname', 'email', 'phone', 'title', 'national_id', 'gender', 'designation'])
        );

        if ($result['status'] === 'error') {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Delete delegate
     */
    public function deleteDelegate($delegateId): JsonResponse
    {
        $result = $this->publicWorkshopService->deleteDelegate($delegateId);

        if ($result['status'] === 'error') {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Download order PDF
     */
    public function downloadOrder($orderId)
    {
        $result = $this->publicWorkshopService->downloadOrder($orderId);

        if ($result['status'] === 'error') {
            return response()->json($result, 404);
        }

        return response()->streamDownload(function () use ($result) {
            echo $result['pdf']->output();
        }, $result['filename']);
    }

    /**
     * Calculate amount due
     */
    public function calculateAmount(Request $request): JsonResponse
    {
        $request->validate([
            'delegates' => 'required|numeric|min:1',
            'workshop_cost' => 'required|numeric|min:0',
            'exchange_rate_id' => 'required|exists:exchangerates,id'
        ]);

        $amountDue = $this->publicWorkshopService->calculateAmountDue(
            $request->delegates,
            $request->workshop_cost,
            $request->exchange_rate_id
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'amount_due' => $amountDue
            ]
        ]);
    }

    /**
     * Get order with delegate count
     */
    public function getOrderWithDelegateCount($orderId): JsonResponse
    {
        $result = $this->publicWorkshopService->getOrderWithDelegateCount($orderId);

        if ($result['status'] === 'error') {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    public function previewDocument($id)
    {
        try {
            $workshop = Workshop::findOrFail($id);

            if (!$workshop->document_url) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No document available for this workshop'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $workshop->document_url);

            if (!file_exists($filePath)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Document file not found'
                ], 404);
            }

            return response()->file($filePath);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}

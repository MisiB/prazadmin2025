<?php

use App\Http\Controllers\Api\IssueTicketController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BanktransactionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EpaymentController;
use App\Http\Controllers\ExchangerateController;
use App\Http\Controllers\InventoryitemController;
use App\Http\Controllers\invoiceController;
use App\Http\Controllers\OnlinepaymentController;
use App\Http\Controllers\PayeeController;
use App\Http\Controllers\PaynowController;
use App\Http\Controllers\PublicWorkshopController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::post('sendPayment', [BanktransactionController::class, 'create'])->name('sendPayment');
Route::get('CheckInvoicenumber/{invoicenumber}', [EpaymentController::class, 'checkinvoice']);
Route::post('PostTransaction', [EpaymentController::class, 'posttransaction']);
Route::get('recallPayment/{referencenumber}', [BanktransactionController::class, 'recallpayment'])->name('recallPayment');
Route::post('BankTransaction/Search', [BanktransactionController::class, 'search'])->name('banktransaction.search');
Route::post('BankTransaction/Claims', [BanktransactionController::class, 'claim'])->name('banktransaction.claim');
Route::get('account', [CustomerController::class, 'index']);
Route::get('account/getbyregnumber/{regnumber}', [CustomerController::class, 'getbyregnumber']);
Route::post('account/Verification', [CustomerController::class, 'verifycustomer']);
Route::post('account', [CustomerController::class, 'createcustomer']);
Route::put('account', [CustomerController::class, 'updatecustomer']);
Route::get('InventoryItem', [InventoryitemController::class, 'getinventories']);
Route::post('WalletBalanceUtilization', [invoiceController::class, 'settleinvoice']);

Route::get('Invoice/{invoicenumber}', [invoiceController::class, 'show']);
Route::post('Invoice/Create', [invoiceController::class, 'store']);
Route::get('ExchangeRate/GetLatest/{currency_id?}', [ExchangerateController::class, 'getlatest']);
Route::delete('Invoice/{invoicenumber}', [invoiceController::class, 'destroy']);
Route::post('Wallet', [WalletController::class, 'getwalletbalance']);
Route::get('Wallet/{regnumber}', [WalletController::class, 'getwallet']);
Route::post('PayNow/Initiate', [OnlinepaymentController::class, 'initiatePayment']);
Route::get('PayNow/{uuid}', [OnlinepaymentController::class, 'checkPayment']);
Route::get('onlinepayments/{uuid}', [OnlinepaymentController::class, 'getepayment']);
Route::post('payees/getbyemail', [PayeeController::class, 'getbyemail']);
Route::get('payees/{uuid}', [PayeeController::class, 'getbyuuid']);
Route::post('payees', [PayeeController::class, 'create']);
Route::put('payees/{uuid}', [PayeeController::class, 'update']);
Route::post('paynow/{uuid}/verify', [PaynowController::class, 'check']);

// Public Workshop API Routes
Route::prefix('public-workshops')->group(function () {
    // Workshop information
    Route::get('/', [PublicWorkshopController::class, 'getPublishedWorkshops'])->name('public-workshop.list');
    Route::get('/{id}/preview-document', [PublicWorkshopController::class, 'previewDocument'])->name('public-workshop.preview-document');

    // Customer search
    Route::post('/search-customer', [PublicWorkshopController::class, 'searchCustomer'])->name('public-workshop.search-customer');

    // Workshop orders - put these BEFORE the generic /{id} route
    Route::get('/{workshopId}/orders/{customerId}', [PublicWorkshopController::class, 'getWorkshopOrder'])->name('public-workshop.get-order');
    Route::post('/orders', [PublicWorkshopController::class, 'createOrder'])->name('public-workshop.create-order');
    Route::post('/public-orders', [PublicWorkshopController::class, 'createPublicOrder'])->name('public-workshop.create-public-order');
    Route::get('/orders/{orderId}/delegate-info', [PublicWorkshopController::class, 'getOrderWithDelegateCount'])->name('public-workshop.order-delegate-info');
    Route::post('/orders/{orderId}/payment', [PublicWorkshopController::class, 'savePayment'])->name('public-workshop.save-payment');
    Route::get('/orders/{orderId}/download', [PublicWorkshopController::class, 'downloadOrder'])->name('public-workshop.download-order');

    // Exchange rates
    Route::post('/exchange-rates', [PublicWorkshopController::class, 'getExchangeRates'])->name('public-workshop.exchange-rates');

    // Delegates
    Route::get('/orders/{orderId}/delegates', [PublicWorkshopController::class, 'getDelegates'])->name('public-workshop.get-delegates');
    Route::post('/delegates', [PublicWorkshopController::class, 'createDelegate'])->name('public-workshop.create-delegate');
    Route::put('/delegates/{delegateId}', [PublicWorkshopController::class, 'updateDelegate'])->name('public-workshop.update-delegate');
    Route::delete('/delegates/{delegateId}', [PublicWorkshopController::class, 'deleteDelegate'])->name('public-workshop.delete-delegate');

    // Utility
    Route::post('/calculate-amount', [PublicWorkshopController::class, 'calculateAmount'])->name('public-workshop.calculate-amount');

    // Put the generic /{id} route LAST
    Route::get('/{id}', [PublicWorkshopController::class, 'getWorkshop'])->name('public-workshop.show');
});

// Issue Ticket API Routes
Route::prefix('helpdesk')->group(function () {
    // Public endpoints - no authentication required
    Route::get('/settings', [SettingController::class, 'getsettings'])->name('api.helpdesk.settings');
       // Ticket creation - no authentication required
    Route::post('/tickets', [IssueTicketController::class, 'store'])->name('api.helpdesk.tickets.create');

    // Ticket tracking by email - no authentication required
    Route::post('/tickets/track', [IssueTicketController::class, 'trackByEmail'])->name('api.helpdesk.tickets.track');

    // Ticket tracking by ticket number - no authentication required
    Route::get('/tickets/number/{ticketNumber}', [IssueTicketController::class, 'showByTicketNumber'])->name('api.helpdesk.tickets.show-by-number');

    // Protected endpoints - require Sanctum authentication
  
        // List all tickets with filters
        Route::get('/tickets/{email}', [IssueTicketController::class, 'index'])->name('api.helpdesk.tickets.index');

        // Get specific ticket by ID
        Route::get('/tickets/{id}/show', [IssueTicketController::class, 'show'])->name('api.helpdesk.tickets.show');

        // Update ticket status
        Route::patch('/tickets/{id}/status', [IssueTicketController::class, 'updateStatus'])->name('api.helpdesk.tickets.update-status');

        // Get statistics
        Route::get('/statistics', [IssueTicketController::class, 'statistics'])->name('api.helpdesk.statistics');

    // Comment endpoints
    // Get all comments for a specific issue
    Route::get('/tickets/{issueId}/comments', [IssueTicketController::class, 'getComments'])->name('api.helpdesk.comments.index');

    // Add a new comment to an issue
    Route::post('/comments', [IssueTicketController::class, 'addComment'])->name('api.helpdesk.comments.store');

    // Update a comment
    Route::put('/comments/{commentId}', [IssueTicketController::class, 'updateComment'])->name('api.helpdesk.comments.update');

    // Delete a comment
    Route::delete('/comments/{commentId}', [IssueTicketController::class, 'deleteComment'])->name('api.helpdesk.comments.destroy');
 
});

// Knowledge Base API Routes
Route::prefix('knowledge-base')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'index'])->name('api.kb.index');
    Route::get('/search', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'search'])->name('api.kb.search');
    Route::get('/featured', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'featured'])->name('api.kb.featured');
    Route::get('/categories', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'categories'])->name('api.kb.categories');
    Route::get('/category/{category}', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'byCategory'])->name('api.kb.category');
    Route::get('/{slug}', [\App\Http\Controllers\Api\KnowledgeBaseController::class, 'show'])->name('api.kb.show');
});

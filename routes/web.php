<?php

use App\Livewire\Admin\RecurringTasks;
use App\Livewire\Admin\TaskTemplates;
use App\Livewire\Admin\Workflows\Approvals\Emailapproval;
use App\Livewire\Admin\Workflows\Approvals\Storesrequisitionacceptance;
use App\Livewire\Admin\Workflows\Approvals\Storesrequisitionapproval;
use App\Livewire\Admin\Workflows\Approvals\Storesrequisitionverification;
use App\Livewire\Admin\Workflows\Leaverequests;
use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('/', 'auth.login')->name('welcome');
Volt::route('/login', 'auth.login')->name('login');
Volt::route('/forgot', 'auth.forgot')->name('auth.forgot');
Volt::route('/connect', 'auth.connect')->name('connect');
Volt::route('/reset/{token}', 'auth.resetpassword')->name('auth.reset');
Route::middleware('auth')->group(function () {
    Route::get('/logout', function () {
        try {
            MsGraph::disconnect();
        } catch (\Exception $e) {
            // Log error but continue with logout
            Log::warning('MsGraph disconnect failed during logout: '.$e->getMessage());
        }
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    Volt::route('/home', 'admin.home')->name('admin.home');
    Volt::route('/settings', 'profile.settings')->name('profile.settings');
    Volt::route('/configuration/accounttypes', 'admin.configuration.accounttypes')->name('admin.configuration.accounttypes');
    Volt::route('/configuration/roles', 'admin.configuration.roles')->name('admin.configuration.roles');
    Volt::route('/configuration/modules', 'admin.configuration.modules')->name('admin.configuration.modules');
    Volt::route('/configuration/departments', 'admin.configuration.departments')->name('admin.configuration.departments');
    Volt::route('/configuration/submodules/{id}', 'admin.configuration.submodules')->name('admin.configuration.submodules');
    Volt::route('/configuration/users', 'admin.configuration.users')->name('admin.configuration.users');
    Volt::route('/configuration/user/{id}', 'admin.configuration.user')->name('admin.configuration.user');
    Volt::route('/configuration/leavetypes', 'admin.configuration.leavetypes')->name('admin.configuration.leavetypes');
    Route::get('/configuration/ts-allowance-configs', \App\Livewire\Admin\Configuration\TsAllowanceConfigs::class)->name('admin.configuration.ts-allowance-configs');
    Route::get('/configuration/staff-welfare-loan-configs', \App\Livewire\Admin\Configuration\StaffWelfareLoanConfigs::class)->name('admin.configuration.staff-welfare-loan-configs');
    Route::get('/configuration/payment-voucher-configs', \App\Livewire\Admin\Configuration\PaymentVoucherConfigs::class)->name('admin.configuration.payment-voucher-configs');
    Volt::route('/configuration/issues', 'admin.configuration.issue-configuration')->name('admin.configuration.issues');
    Volt::route('/finances/configurations', 'admin.finance.configuration')->name('admin.finance.configurations');
    Volt::route('/finances/reports', 'admin.finance.reports.finacereports')->name('admin.finance.reports');
    Volt::route('/finances/bankreconciliationreport/{id}', 'admin.finance.reports.bankreconciliationreport')->name('admin.finance.reports.bankreconciliationreport');
    Volt::route('/finances/suspensereports', 'admin.finance.suspensereports')->name('admin.finance.suspensereports');
    Volt::route('/finances/banktransactions', 'admin.finance.banktransactions')->name('admin.finance.banktransactions');
    Volt::route('/finances/wallettopups', 'admin.finance.wallettopuprequest')->name('admin.finance.wallettopups');
    Volt::route('/finances/workshopinvoices', 'admin.finance.workshops')->name('admin.finance.workshops');
    Volt::route('/procurements/tenders', 'admin.procurements.tenders')->name('admin.procurements.tenders');
    Volt::route('/procurements/tendertype', 'admin.procurements.tendertype')->name('admin.procurements.tendertypes');
    Volt::route('/strategies', 'admin.management.strategies')->name('admin.management.strategies');
    Volt::route('/subprogrammeoutputs', 'admin.management.subprogrammeoutputs')->name('admin.management.subprogrammeoutputs');
    Volt::route('/workplans', 'admin.management.workplans')->name('admin.management.workplans');
    Volt::route('/workplanreviews', 'admin.management.workplanreviews')->name('admin.management.workplanreviews');
    Volt::route('/budgetconfigurations', 'admin.finance.budgetconfigurations.configurationlist')->name('admin.finance.budgetconfigurations.configurationlist');
    Volt::route('/strategydetail/{uuid}', 'admin.management.strategydetail')->name('admin.management.strategydetail');
    Volt::route('/strategyprogrammeoutcomes/{uuid}/{programme_id}', 'admin.management.strategyprogrammeoutcomes')->name('admin.management.strategyprogrammeoutcomes');
    Volt::route('/strategyprogrammeoutcomeindicators/{uuid}/{programme_id}/{outcome_id}', 'admin.management.strategyprogrammeoutcomeindicators')->name('admin.management.strategyprogrammeoutcomeindicators');
    Volt::route('/customers', 'admin.customers.showlist')->name('admin.customers.showlist');
    Volt::route('/budgets', 'admin.finance.budgetmanagement.budgets')->name('admin.finance.budgetmanagement.budgets');
    Volt::route('/budgetdetail/{uuid}', 'admin.finance.budgetmanagement.budgetdetail')->name('admin.finance.budgetmanagement.budgetdetail');
    Volt::route('/departmentalbudgets', 'admin.finance.budgetmanagement.departmentalbudget')->name('admin.finance.budgetmanagement.departmentalbudgets');
    Volt::route('/departmentalbudgetdetail/{uuid}', 'admin.finance.budgetmanagement.departmentalbudgetdetail')->name('admin.finance.budgetmanagement.departmentalbudgetdetail');
    Volt::route('/customers/{id}', 'admin.customers.show')->name('admin.customers.show');
    Volt::route('/customers/{customer_id}/invoices', 'admin.customers.components.invoices')->name('admin.customers.showinvoices');
    Volt::route('/customers/{customer_id}/banktransactions', 'admin.customers.components.banktransactions')->name('admin.customers.showbanktransactions');
    Volt::route('/customers/{customer_id}/epayments', 'admin.customers.components.epayments')->name('admin.customers.showepayments');
    Volt::route('/customers/{customer_id}/onlinepayments', 'admin.customers.components.onlinepayments')->name('admin.customers.showonlinepayments');
    Volt::route('/customers/{customer_id}/wallettopups', 'admin.customers.components.wallettops')->name('admin.customers.showwallettopups');
    Volt::route('/customers/{customer_id}/suspensestatement', 'admin.customers.components.suspensestatement')->name('admin.customers.showsuspensestatement');
    Volt::route('/customers/{customer_id}/reversedtransactions', 'admin.customers.components.reversed-transactions')->name('admin.customers.showreversedtransactions');
    Volt::route('/workflows', 'admin.workflows.configurations')->name('admin.workflows');
    Volt::route('/purchaserequisitions', 'admin.workflows.purchaserequisitions')->name('admin.workflows.purchaserequisitions');
    Volt::route('/weekytasks', 'admin.workflows.approvals.weekytasks')->name('admin.workflows.approvals.weekytasks');
    Volt::route('/purchaserequisition/{uuid}', 'admin.workflows.purchaserequisition')->name('admin.workflows.purchaserequisition');
    Volt::route('/awaitingpmu', 'admin.workflows.awaitingpmu')->name('admin.workflows.awaitingpmu');
    Volt::route('/approvals/purchaserequisitionlist', 'admin.workflows.approvals.purchaserequisitionlist')->name('admin.workflows.approvals.purchaserequisitionlist');
    Volt::route('/approvals/purchaserequisitionshow/{uuid}', 'admin.workflows.approvals.purchaserequisitionshow')->name('admin.workflows.approvals.purchaserequisitionshow');
    Route::get('/paymentrequisitions', \App\Livewire\Admin\Workflows\PaymentRequisitions::class)->name('admin.paymentrequisitions');
    Route::get('/paymentrequisition/{uuid}', \App\Livewire\Admin\Workflows\PaymentRequisitionshow::class)->name('admin.paymentrequisition');
    Route::get('/approvals/paymentrequisitionlist', \App\Livewire\Admin\Workflows\Approvals\PaymentRequisitionlist::class)->name('admin.workflows.approvals.paymentrequisitionlist');

    // Payment Voucher Routes
    Route::get('/paymentvouchers', \App\Livewire\Admin\Workflows\PaymentVouchers::class)->name('admin.paymentvouchers');
    Route::get('/paymentvoucher/{uuid}', \App\Livewire\Admin\Workflows\PaymentVouchershow::class)->name('admin.paymentvoucher.show');
    Route::get('/approvals/paymentvoucherlist', \App\Livewire\Admin\Workflows\Approvals\PaymentVoucherlist::class)->name('admin.workflows.approvals.paymentvoucherlist');
    Volt::route('/awaitingdelivery', 'admin.workflows.awaitingdelivary')->name('admin.workflows.awaitingdelivery');
    Volt::route('/finances/revenueposting', 'admin.finance.revenueposting')->name('admin.finance.revenueposting');
    Volt::route('/workflows/leavestatements', 'admin.workflows.leavestatements')->name('admin.workflows.leavestatements');
    // Volt::route('/workflows/leaverequests', 'admin.workflows.leaverequests')->name('admin.workflows.leaverequests');
    Route::match(['get', 'post'], '/workflows/leaverequests', Leaverequests::class)->name('admin.workflows.leaverequests');
    Volt::route('/workflows/storesrequisitions', 'admin.workflows.storesrequisitions')->name('admin.workflows.storesrequisitions');
    Volt::route('/approvals/storesrequisitions', 'admin.workflows.approvals.deptstoresrequisitionapprovals')->name('admin.workflows.approvals.deptstoresrequisitionapprovals');
    Volt::route('/approvals/storesrequisitiondelivery', 'admin.workflows.approvals.storesrequisitiondelivery')->name('admin.workflows.approvals.storesrequisitiondelivery');
    //Trackers routes
    Volt::route('/trackers/performancetracker', 'admin.trackers.performancetracker')->name('admin.trackers.performancetracker');
    Volt::route('/trackers/budgettracker', 'admin.trackers.budgettracker')->name('admin.trackers.budgettracker');
    Volt::route('/trackers/departmentaldashboard', 'admin.trackers.departmentaldashboard')->name('admin.trackers.departmentaldashboard');
    Volt::route('/trackers/organisationdashboard', 'admin.trackers.organisationdashboard')->name('admin.trackers.organisationdashboard');
    // M'n'e admin dashboard routes
    Volt::route('/trackers/returnsoverview', 'admin.trackers.returnsoverview')->name('admin.trackers.returnsoverview');
    
    Volt::route('/calendar', 'admin.weekday-calendar')->name('admin.calendar');
    Volt::route('/issues', 'admin.issues')->name('admin.issues');
    Volt::route('/myissues', 'admin.my-issues')->name('admin.myissues');
    Volt::route('/departmentalissues', 'admin.departmental-issues')->name('admin.departmentalissues');
    Volt::route('/issuetracker', 'admin.issue-tracker')->name('admin.issuetracker');
    Route::get('/knowledge-base', \App\Livewire\Admin\KnowledgeBaseManagement::class)->name('admin.knowledge-base');
    Route::get('/email-processor', \App\Livewire\Admin\EmailProcessor::class)->name('admin.email-processor');
    Volt::route('/weekly-task-review', 'admin.weekly-task-review')->name('admin.weeklytaskreview');
    Volt::route('/workshopindex', 'admin.workshops.workshopindex')->name('admin.workshop.index');
    Volt::route('/workshopindex/{id}', 'admin.workshops.workshopview')->name('admin.workshop.view');

    // Task Templates and Recurring Tasks
    Route::get('/tasks/templates', TaskTemplates::class)->name('admin.tasks.templates');
    Route::get('/tasks/recurring', RecurringTasks::class)->name('admin.tasks.recurring');

    // Staff Welfare Loan Routes
    Route::get('/workflows/staff-welfare-loans', \App\Livewire\Admin\Workflows\StaffWelfareLoans::class)->name('admin.workflows.staff-welfare-loans');
    Route::get('/workflows/staff-welfare-loan/{uuid}', \App\Livewire\Admin\Workflows\StaffWelfareLoan::class)->name('admin.workflows.staff-welfare-loan');
    Route::get('/workflows/approvals/staff-welfare-loanlist', \App\Livewire\Admin\Workflows\Approvals\StaffWelfareLoanlist::class)->name('admin.workflows.approvals.staff-welfare-loanlist');
    Route::get('/workflows/approvals/staff-welfare-loan-payments', \App\Livewire\Admin\Workflows\Approvals\StaffWelfareLoanPayments::class)->name('admin.workflows.approvals.staff-welfare-loan-payments');
    Route::get('/workflows/reports/staff-welfare-loans', \App\Livewire\Admin\Workflows\Reports\StaffWelfareLoanReport::class)->name('admin.workflows.reports.staff-welfare-loans');

    // T&S Allowances Routes
    Route::get('/workflows/ts-allowances', \App\Livewire\Admin\Workflows\TsAllowances::class)->name('admin.workflows.ts-allowances');
    Route::get('/workflows/ts-allowance/{uuid}', \App\Livewire\Admin\Workflows\TsAllowance::class)->name('admin.workflows.ts-allowance');
    Route::get('/workflows/approvals/ts-allowancelist', \App\Livewire\Admin\Workflows\Approvals\TsAllowancelist::class)->name('admin.workflows.approvals.ts-allowancelist');
    Route::get('/workflows/approvals/ts-allowance-finance', \App\Livewire\Admin\Workflows\Approvals\TsAllowancePayments::class)->name('admin.workflows.approvals.ts-allowance-finance');
    Route::get('/workflows/reports/ts-allowances', \App\Livewire\Admin\Workflows\Reports\TsAllowanceReport::class)->name('admin.workflows.reports.ts-allowances');

    // Purchase Requisition Report Routes
    Route::get('/workflows/reports/purchase-requisitions', \App\Livewire\Admin\Workflows\Reports\PurchaseRequisitionReport::class)->name('admin.workflows.reports.purchase-requisitions');

    // Payment Requisition Report Routes
    Route::get('/workflows/reports/payment-requisitions', \App\Livewire\Admin\Workflows\Reports\PaymentRequisitionReport::class)->name('admin.workflows.reports.payment-requisitions');

    // Payment Voucher Report Routes
    Route::get('/workflows/reports/payment-vouchers', \App\Livewire\Admin\Workflows\Reports\PaymentVoucherReport::class)->name('admin.workflows.reports.payment-vouchers');
});
// Email Approval Flows
Route::get('/approval/{leaveapprovalitemuuid}/{leaveapproverid}/{storesapprovalitemuuid}/{storesapproverid}/{status}', function ($leaveapprovalitemuuid, $leaveapproverid, $storesapprovalitemuuid, $storesapproverid, $status) {
    $msgraph = new MsGraph;

    return $msgraph::emailapprovalconnect($leaveapprovalitemuuid, $leaveapproverid, $storesapprovalitemuuid, $storesapproverid, $status);
});
Volt::route('/leaverequestapproval/{approvalrecordid}/{approvalitemuuid}', Emailapproval::class)->name('leaverequest.email.auth.approval');
Volt::route('/requisitionapproval/{approvalrecordid}/{approvalitemuuid}', Storesrequisitionapproval::class)->name('storesrequisition.email.auth.approval');
Volt::route('/requisitionacceptance/{approvalrecordid}/{approvalitemuuid}', Storesrequisitionacceptance::class)->name('storesrequisition.email.auth.acceptance');
Volt::route('/requisitionverification/{approvalrecordid}/{approvalitemuuid}', Storesrequisitionverification::class)->name('storesrequisition.email.auth.verification');

// Support Dashboard - Limited access for consultants
Volt::route('/support/dashboard', 'support.dashboard')
    ->middleware('can:support.access')
    ->name('support.dashboard');

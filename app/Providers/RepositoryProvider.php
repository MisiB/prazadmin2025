<?php

namespace App\Providers;

use App\implementation\repositories\_accountsettingRepository;
use App\implementation\repositories\_accounttypeRepository;
use App\implementation\repositories\_adminstoresrequisitionapprovalRepository;
use App\implementation\repositories\_authRepository;
use App\implementation\repositories\_bankaccountRepository;
use App\implementation\repositories\_bankRepository;
use App\implementation\repositories\_banktransactionRepository;
use App\implementation\repositories\_budgetconfigurationRepository;
use App\implementation\repositories\_budgetRepository;
use App\implementation\repositories\_calenderRepository;
use App\implementation\repositories\_currencyRepository;
use App\implementation\repositories\_customerRepository;
use App\implementation\repositories\_departmentRepository;
use App\implementation\repositories\_epaymentRepository;
use App\implementation\repositories\_exchangerateRepository;
use App\implementation\repositories\_hodstoresrequisitionapprovalRepository;
use App\implementation\repositories\_inventoryitemRepository;
use App\implementation\repositories\_invoiceRepository;
use App\implementation\repositories\_issuegroupRepository;
use App\implementation\repositories\_issuelogRepository;
use App\implementation\repositories\_issuerstoresrequisitionapprovalRepository;
use App\implementation\repositories\_issuetypeRepository;
use App\implementation\repositories\_leaverequestapprovalRepository;
use App\implementation\repositories\_leaverequestRepository;
use App\implementation\repositories\_leavestatementRepository;
use App\implementation\repositories\_leavetypeRepository;
use App\implementation\repositories\_moduleRepository;
use App\implementation\repositories\_onlinepaymentRepository;
use App\implementation\repositories\_payeeRepository;
use App\implementation\repositories\_paynowintegrationsRepository;
use App\implementation\repositories\_permissionRepository;
use App\implementation\repositories\_purchaserequisitionRepository;
use App\implementation\repositories\_receiverstoresrequisitionapprovalRepository;
use App\implementation\repositories\_revenuepostingRepository;
use App\implementation\repositories\_roleRepository;
use App\implementation\repositories\_storesrequisitionRepository;
use App\implementation\repositories\_strategyRepository;
use App\implementation\repositories\_submoduleRepository;
use App\implementation\repositories\_subprogrammeoutputRepository;
use App\implementation\repositories\_suspenseRepository;
use App\implementation\repositories\_taskRepository;
use App\implementation\repositories\_tenderRepository;
use App\implementation\repositories\_userRepository;
use App\implementation\repositories\_wallettopupRepository;
use App\implementation\repositories\_workflowRepository;
use App\implementation\repositories\_workplanRepository;
use App\implementation\repositories\_workshopRepository;
use App\Interfaces\repositories\iaccountsettingInterface;
use App\Interfaces\repositories\iaccounttypeInterface;
use App\Interfaces\repositories\iadminstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\repositories\ibankaccountInterface;
use App\Interfaces\repositories\ibankInterface;
use App\Interfaces\repositories\ibanktransactionInterface;
use App\Interfaces\repositories\ibudgetconfigurationInterface;
use App\Interfaces\repositories\ibudgetInterface;
use App\Interfaces\repositories\icalendarInterface;
use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\icustomerInterface;
use App\Interfaces\repositories\idepartmentInterface;
use App\Interfaces\repositories\iepaymentInterface;
use App\Interfaces\repositories\iexchangerateInterface;
use App\Interfaces\repositories\ihodstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\iinventoryitemInterface;
use App\Interfaces\repositories\iissuegroupInterface;
use App\Interfaces\repositories\iissuelogInterface;
use App\Interfaces\repositories\iissuerstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\iissuetypeInterface;
use App\Interfaces\repositories\ileaverequestapprovalInterface;
use App\Interfaces\repositories\ileaverequestInterface;
use App\Interfaces\repositories\ileavestatementInterface;
use App\Interfaces\repositories\ileavetypeInterface;
use App\Interfaces\repositories\imoduleInterface;
use App\Interfaces\repositories\invoiceInterface;
use App\Interfaces\repositories\ionlinepaymentInterface;
use App\Interfaces\repositories\ipayeeInterface;
use App\Interfaces\repositories\ipaynowintegrationsInterface;
use App\Interfaces\repositories\ipermissionInterface;
use App\Interfaces\repositories\ipurchaseerequisitionInterface;
use App\Interfaces\repositories\ireceiverstoresrequisitionapprovalInterface;
use App\Interfaces\repositories\irevenuepostingInterface;
use App\Interfaces\repositories\iroleRepository;
use App\Interfaces\repositories\istoresrequisitionInterface;
use App\Interfaces\repositories\istrategyInterface;
use App\Interfaces\repositories\isubmoduleInterface;
use App\Interfaces\repositories\isubprogrammeoutInterface;
use App\Interfaces\repositories\isuspenseInterface;
use App\Interfaces\repositories\itaskInterface;
use App\Interfaces\repositories\itenderInterface;
use App\Interfaces\repositories\iuserInterface;
use App\Interfaces\repositories\iwallettopupInterface;
use App\Interfaces\repositories\iworkflowInterface;
use App\Interfaces\repositories\iworkplanInterface;
use App\Interfaces\repositories\iworkshopInterface;
use App\Interfaces\repositories\individualworkplanInterface;
use App\Interfaces\repositories\iIndicatorInterface;
use App\Interfaces\repositories\iprogrammeInterface;
use App\Interfaces\services\iAzureEmailServiceInterface;
use App\Services\AzureEmailService;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\repositories\ioutcomeInterface;
use App\Interfaces\repositories\ioutputInterface;
use App\implementation\repositories\_outcomeRepository;
use App\implementation\repositories\_outputRepository;
use App\implementation\repositories\_indicatorRepository;
use App\Interfaces\repositories\istrategylogInterface;
use App\implementation\repositories\_strategylogRepository;
use App\implementation\repositories\_programmeRepository;
use App\Interfaces\repositories\iTargetmatrixInterface;
use App\implementation\repositories\_targetmatrixRepository;
use App\implementation\repositories\_individualworkplanRepository;
class RepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(iaccountsettingInterface::class, _accountsettingRepository::class);
        $this->app->bind(iuserInterface::class, _userRepository::class);
        $this->app->bind(iaccounttypeInterface::class, _accounttypeRepository::class);
        $this->app->bind(imoduleInterface::class, _moduleRepository::class);
        $this->app->bind(ipermissionInterface::class, _permissionRepository::class);
        $this->app->bind(iroleRepository::class, _roleRepository::class);
        $this->app->bind(isubmoduleInterface::class, _submoduleRepository::class);
        $this->app->bind(iauthInterface::class, _authRepository::class);
        $this->app->bind(icurrencyInterface::class, _currencyRepository::class);
        $this->app->bind(ibankInterface::class, _bankRepository::class);
        $this->app->bind(ibankaccountInterface::class, _bankaccountRepository::class);
        $this->app->bind(iinventoryitemInterface::class, _inventoryitemRepository::class);
        $this->app->bind(ipaynowintegrationsInterface::class, _paynowintegrationsRepository::class);
        $this->app->bind(iexchangerateInterface::class, _exchangerateRepository::class);
        $this->app->bind(icustomerInterface::class, _customerRepository::class);
        $this->app->bind(isuspenseInterface::class, _suspenseRepository::class);
        $this->app->bind(ibanktransactionInterface::class, _banktransactionRepository::class);
        $this->app->bind(invoiceInterface::class, _invoiceRepository::class);
        $this->app->bind(itenderInterface::class, _tenderRepository::class);
        $this->app->bind(iepaymentInterface::class, _epaymentRepository::class);
        $this->app->bind(ionlinepaymentInterface::class, _onlinepaymentRepository::class);
        $this->app->bind(iwallettopupInterface::class, _wallettopupRepository::class);
        $this->app->bind(idepartmentInterface::class, _departmentRepository::class);
        $this->app->bind(istrategyInterface::class, _strategyRepository::class);
        $this->app->bind(isubprogrammeoutInterface::class, _subprogrammeoutputRepository::class);
        $this->app->bind(iworkplanInterface::class, _workplanRepository::class);
        $this->app->bind(itaskInterface::class, _taskRepository::class);
        $this->app->bind(ibudgetconfigurationInterface::class, _budgetconfigurationRepository::class);
        $this->app->bind(ibudgetInterface::class, _budgetRepository::class);
        $this->app->bind(ipurchaseerequisitionInterface::class, _purchaserequisitionRepository::class);
        $this->app->bind(iworkflowInterface::class, _workflowRepository::class);
        $this->app->bind(irevenuepostingInterface::class, _revenuepostingRepository::class);
        $this->app->bind(ileavetypeInterface::class, _leavetypeRepository::class);
        $this->app->bind(ileavestatementInterface::class, _leavestatementRepository::class);
        $this->app->bind(ileaverequestInterface::class, _leaverequestRepository::class);
        $this->app->bind(ileaverequestapprovalInterface::class, _leaverequestapprovalRepository::class);
        $this->app->bind(istoresrequisitionInterface::class, _storesrequisitionRepository::class);
        $this->app->bind(ihodstoresrequisitionapprovalInterface::class, _hodstoresrequisitionapprovalRepository::class);
        $this->app->bind(iissuerstoresrequisitionapprovalInterface::class, _issuerstoresrequisitionapprovalRepository::class);
        $this->app->bind(ireceiverstoresrequisitionapprovalInterface::class, _receiverstoresrequisitionapprovalRepository::class);
        $this->app->bind(iadminstoresrequisitionapprovalInterface::class, _adminstoresrequisitionapprovalRepository::class);
        $this->app->bind(icalendarInterface::class, _calenderRepository::class);

        //   $this->app->bind(ihodstoresrequisitionapprovalInterface::class, _hodstoresrequisitionapprovalRepository::class);
        $this->app->bind(iworkshopInterface::class, _workshopRepository::class);
        $this->app->bind(ipayeeInterface::class, _payeeRepository::class);
        $this->app->bind(iissuelogInterface::class, _issuelogRepository::class);
        $this->app->bind(iissuegroupInterface::class, _issuegroupRepository::class);
        $this->app->bind(iissuetypeInterface::class, _issuetypeRepository::class);
        $this->app->bind(\App\Interfaces\repositories\iknowledgeBaseInterface::class, \App\implementation\repositories\_knowledgeBaseRepository::class);
        $this->app->bind(iAzureEmailServiceInterface::class, AzureEmailService::class);
        $this->app->bind(ioutcomeInterface::class, _outcomeRepository::class);
        $this->app->bind(ioutputInterface::class, _outputRepository::class);
        $this->app->bind(iIndicatorInterface::class, _indicatorRepository::class);
        $this->app->bind(istrategylogInterface::class, _strategylogRepository::class);
        $this->app->bind(iprogrammeInterface::class, _programmeRepository::class);
        $this->app->bind(iTargetmatrixInterface::class, _targetmatrixRepository::class);
        $this->app->bind(individualworkplanInterface::class, _individualworkplanRepository::class);
    }
}

<?php

namespace App\Providers;

use App\implementation\services\_banktransactionService;
use App\implementation\services\_calendarService;
use App\implementation\services\_customerService;
use App\implementation\services\_epaymentService;
use App\implementation\services\_exchangerateService;
use App\implementation\services\_httpService;
use App\implementation\services\_importService;
use App\implementation\services\_inventoryitemService;
use App\implementation\services\_invoiceService;
use App\implementation\services\_onlinepaymentService;
use App\implementation\services\_palladiumService;
use App\implementation\services\_payeeService;
use App\implementation\services\_paynowService;
use App\implementation\services\_storesrequisitionService;
use App\implementation\services\_suspenseService;
use App\implementation\services\_tenderService;
use App\implementation\services\_workshopService;
use App\Interfaces\services\ibanktransactionInterface;
use App\Interfaces\services\ICalendarService;
use App\Interfaces\services\icustomerInterface;
use App\Interfaces\services\iepaymentService;
use App\Interfaces\services\iexchangerateService;
use App\Interfaces\services\ihttpInterface;
use App\Interfaces\services\IImportService;
use App\Interfaces\services\iinventoryitemService;
use App\Interfaces\services\iinvoiceService;
use App\Interfaces\services\ionlinepaymentService;
use App\Interfaces\services\ipalladiumInterface;
use App\Interfaces\services\ipayeeService;
use App\Interfaces\services\ipaynowInterface;
use App\Interfaces\services\istoresrequisitionService;
use App\Interfaces\services\isuspenseService;
use App\Interfaces\services\itenderService;
use App\Interfaces\services\iworkshopService;
use Illuminate\Support\ServiceProvider;

class ApiProvider extends ServiceProvider
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
        $this->app->bind(ihttpInterface::class, _httpService::class);
        $this->app->bind(ipaynowInterface::class, _paynowService::class);
        $this->app->bind(IImportService::class, _importService::class);
        $this->app->bind(ipalladiumInterface::class, _palladiumService::class);
        $this->app->bind(ICalendarService::class, _calendarService::class);
        $this->app->bind(ibanktransactionInterface::class, _banktransactionService::class);
        $this->app->bind(icustomerInterface::class, _customerService::class);
        $this->app->bind(iinventoryitemService::class, _inventoryitemService::class);
        $this->app->bind(iinvoiceService::class, _invoiceService::class);
        $this->app->bind(iexchangerateService::class, _exchangerateService::class);
        $this->app->bind(isuspenseService::class, _suspenseService::class);
        $this->app->bind(istoresrequisitionService::class, _storesrequisitionService::class);
        $this->app->bind(ionlinepaymentService::class, _onlinepaymentService::class);
        $this->app->bind(itenderService::class, _tenderService::class);
        $this->app->bind(iepaymentService::class, _epaymentService::class);
        $this->app->bind(ipayeeService::class, _payeeService::class);
        $this->app->bind(iworkshopService::class, _workshopService::class);
    }
}

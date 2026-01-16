<?php

namespace App\implementation\services;

use App\Interfaces\repositories\icurrencyInterface;
use App\Interfaces\repositories\ischoolexpensecategoryInterface;
use App\Interfaces\repositories\ischoolInterface;
use App\Interfaces\repositories\ischoolmonthlyreturndataInterface;
use App\Interfaces\repositories\ischoolmonthlyreturnInterface;
use App\Interfaces\services\ischoolService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class _schoolService implements ischoolService
{
    protected $currencyRepository;
    protected $schoolmonthlyreturnRepository;
    protected $schoolexpensecategoryRepository;
    protected $schoolmonthlyreturndataRepository;
    protected $schoolRepository;
    public $year;
    public $month;
    public $activecurrencystatus;

    public function __construct(icurrencyInterface $currencyRepository, ischoolmonthlyreturnInterface $schoolmonthlyreturnRepository, 
    ischoolexpensecategoryInterface $schoolexpensecategoryRepository, ischoolmonthlyreturndataInterface $schoolmonthlyreturndataRepository,
    ischoolInterface $schoolRepository)
    {
        $this->currencyRepository = $currencyRepository;
        $this->schoolmonthlyreturnRepository = $schoolmonthlyreturnRepository;
        $this->schoolexpensecategoryRepository = $schoolexpensecategoryRepository;
        $this->schoolmonthlyreturndataRepository=$schoolmonthlyreturndataRepository;
        $this->schoolRepository=$schoolRepository;
        $this->year = Carbon::now()->year;
        $this->month = Carbon::now()->englishMonth;
        $this->activecurrencystatus="ACTIVE";
    }

    
    public function gettotalexpenditure($schoolnumber, $status, $year=null, $month=null)
    {
        
        /**
         * 
         * Get all approved returns, for each approve return add all its expenditures
         *  
         * */
        $totalscollection=[];
        $sum=0;
        collect($this->currencyRepository->getcurrenciesbystatus($this->activecurrencystatus))->map(function ($currency) use (&$sum, &$totalscollection, &$schoolnumber, &$status, &$year, &$month){
            //Get monthly returns by approved status
            $this->schoolmonthlyreturnRepository->getmonthlyreturnsbyschoolnumber($schoolnumber, $status, $year, $month)->map(function ($monthlyreturn) use (&$sum, &$currency){
                collect($monthlyreturn->schoolmonthlyreturndatas)->map(function ($returnsdata) use (&$sum, &$currency) {
                    if((int)$returnsdata->currency_id===(int)$currency['id'])
                    {   
                        $sum = (float) $returnsdata->amount + (float) $sum ;
                    }
                });
            });

            $totalscollection[]=[
                "currency"=>$currency['name'],
                "value"=>$sum
            ];
            $sum=0;   
                    
        });
        return $totalscollection;
    }

    public function  getmonthlist()
    {
        
        return [
            ["id" => "January", "name" => "January"],
            ["id" => "February", "name" => "February"],
            ["id" => "March", "name" => "March"],
            ["id" => "April", "name" => "April"],
            ["id" => "May", "name" => "May"],
            ["id" => "June", "name" => "June"],
            ["id" => "July", "name" => "July"],
            ["id" => "August", "name" => "August"],
            ["id" => "September", "name" => "September"],
            ["id" => "October", "name" => "October"],
            ["id" => "November", "name" => "November"],
            ["id" => "December", "name" => "December"],
        ];
    }

    public function getheaders()
    {
        return [
            ['key' => "schoolexpensecategory", "label" => "Category"],
            ['key' => 'year', "label" => "Year"],
            ['key' => 'month', 'label' => 'Month'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'data', 'label' => 'Expenditure', 'sortable' => false]
        ];
    }
    public  function monthlyreturnheaders()
    {
        return [
            ['key' => "sourceoffund", "label" => "Expenditure source of fund"],
            ['key' => 'currency.name', "label" => "Currency"],
            ['key' => 'amount', "label" => "Amount"],
        ];
    }


    public function exportexcelreport($data, $schoolname)
    {
        // Logic to handle the export of leave statements
        $monthlyreturnreport=[];
        $monthlyreturnreport[]=[
            "category"=>"Monthly Return Category",
            "year"=>"Submission Year", 
            "month"=>"Submission Month", 
            "status"=>"Monthly Return Status", 
            "expendituresrcfund"=>"Expenditure Source of Fund",
            "expenditurecurrency"=>"Expenditure Currency",
            "expenditureamount"=>"Expenditure Amount",
        ];
        
        /**
         * Export loop
         */
        $monthlyreturnmonth=null;
        $monthlyreturnyear=null;
        $monthlyreturnschoolname=$schoolname;
        collect($data)->each(function($monthlyreturn) use (&$monthlyreturnreport, &$monthlyreturnmonth, &$monthlyreturnyear, &$monthlyreturnschoolname){
            if($monthlyreturnmonth==null && $monthlyreturnyear==null)
            {
                $monthlyreturnmonth=$monthlyreturn->month;
                $monthlyreturnyear=$monthlyreturn->year;
            }
            $monthlyreturnmonth=$monthlyreturn->month;
            $monthlyreturnyear=$monthlyreturn->year;
            $monthlyreturn->schoolmonthlyreturndatas->each(function($returndata) use (&$monthlyreturnreport, &$monthlyreturn){
                $monthlyreturnreport[]=[
                    "category"=>$monthlyreturn->schoolexpensecategory->name,
                    "year"=>$monthlyreturn->year, 
                    "month"=>$monthlyreturn->month, 
                    "status"=>$monthlyreturn->status, 
                    "expendituresrcfund"=>$returndata->sourceoffund,
                    "expenditurecurrency"=>$returndata->currency->name,
                    "expenditureamount"=>$returndata->amount,
                ];
            });
        });
        /**
         * Create and download file
         */
        if($monthlyreturnmonth==null && $monthlyreturnyear==null)
        {
            $monthlyreturnmonth=$this->month;
            $monthlyreturnyear=$this->year;
        }
        $filename=$monthlyreturnmonth.' '.$monthlyreturnyear.' _monthly_return_for_'.$monthlyreturnschoolname.'.csv';
        $file=fopen($filename,'w');
        collect($monthlyreturnreport)->each(function($returndata) use (&$file)
        {
            fputcsv($file, $returndata);
        });
        fclose($file);
        return response()->download(public_path($filename))->deleteFileAfterSend(true);

    }

    public function getmonthlyreturns($schoolnumber, $status, $year=null, $month=null, $perpage=null)
    {
        return $this->schoolmonthlyreturnRepository->getmonthlyreturnsbyschoolnumber($schoolnumber, $status, $year, $month, $perpage);
    }

    public function getschoolexpensecategories()
    {
        return $this->schoolexpensecategoryRepository->getexpensecategories();
    }

    public  function  getsourceoffunds()
    {
        return  [
            ['id' => 'SSF', 'name' => 'School Services Fund (SSF)'],
            ['id' => 'SDC', 'name' => 'SDC'],
            ['id' => 'SIG', 'name' => 'School Improvement Grant'],
            ['id' => 'GPF', 'name' => 'General Purpose Fund'],
            ['id' => 'Special Levy', 'name' => 'Special Levy'],
        ];
    }

    public function getcurrenciesbystatus($status)
    {
        return $this->currencyRepository->getcurrenciesbystatus($status);
    }

    public function getmonthlyreturnbyid($monthlyreturnid)
    {
        return $this->schoolmonthlyreturnRepository->getmonthlyreturnbyid($monthlyreturnid);
    }
    public function getmonthlyreturndatabyreturnid($monthlyreturnid, $perpage=null)
    {
        return $this->schoolmonthlyreturndataRepository->getmonthlyreturndatabyreturnid($monthlyreturnid, $perpage);
    }
    
    public function getschoolbynameornumber($schoolname=null, $schoolnumber=null)
    {
        return $this->schoolRepository->getschoolbynameornumber($schoolname, $schoolnumber);
    }
    public function searchschool($schoolname=null, $schoolid=null)
    {

        $recordexists =  $this->getschoolbynameornumber($schoolname, $schoolid);

        if (!$recordexists) {
            return null;
        }
        return $recordexists;
    }
}

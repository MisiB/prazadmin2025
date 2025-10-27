<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\repositories\iissuetypeInterface;
use App\Interfaces\repositories\iissuegroupInterface;

class SettingController extends Controller
{
    protected $issuetypeService;
    protected $issuegroupService;

    public function __construct(iissuegroupInterface $issuegroupService, iissuetypeInterface $issuetypeService)
    {
        $this->issuetypeService = $issuetypeService;
        $this->issuegroupService = $issuegroupService;
    }

    public function getsettings()
    {
        $issuegroups = $this->issuegroupService->getIssueGroups();
        $issuetypes = $this->issuetypeService->getIssueTypes();
        return response()->json( [
                'issuegroups' => $issuegroups,
                'issuetypes' => $issuetypes,
            ],200);
    }

}

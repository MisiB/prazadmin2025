<?php

namespace App\Http\Controllers;

use App\Http\Requests\PayeeRequest;
use App\Http\Requests\UpdatePayeeRequest;
use App\Interfaces\services\ipayeeService;
use Illuminate\Http\Request;

class PayeeController extends Controller
{
    protected $service;

    public function __construct(ipayeeService $service)
    {
        $this->service = $service;
    }

    public function getbyemail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);
        if (! $validated) {
            return response()->json(['error' => 'Invalid email format'], 400);
        }

        return $this->service->getbyemail($validated['email']);
    }

    public function getbyuuid($uuid)
    {
        return $this->service->getbyuuid($uuid);
    }

    public function create(PayeeRequest $request)
    {
        return $this->service->create($request->validated());
    }

    public function update($uuid, UpdatePayeeRequest $request)
    {
        return $this->service->update($request->validated(), $uuid);
    }

    public function retrievepayments(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);
        if (! $validated) {
            return response()->json(['error' => 'Invalid email format'], 400);
        }

        return $this->service->retrievepayments($validated['email']);
    }
}

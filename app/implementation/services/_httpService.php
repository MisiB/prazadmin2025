<?php

namespace App\implementation\services;

use App\Enums\ApiResponse;
use App\Interfaces\ihttpInterface;
use Illuminate\Support\Facades\Http;

class _httpService implements ihttpInterface
{
   
    protected  $api;
    protected $token;
    public function __construct()
    {
        $this->api = config('httpconfig.api');
        $this->token = session('api_token');
    }

    public function getaccounttypes()
    {
        $url = $this->api . '/account-types';
        try {
            $response = Http::accept('application/json')->withToken($this->token)->get($url);           
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

   

    public function getaccounttype($id){
        $url = $this->api . '/account-types/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->get($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function createaccounttype(array $data){
        $url = $this->api . '/account-types';
        try {   
            $response = Http::accept('application/json')->withToken($this->token)->post($url, $data);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function updateaccounttype($id, array $data){
        $url = $this->api . '/account-types/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->put($url, $data);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function deleteaccounttype($id){

        $url = $this->api . '/account-types/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->delete($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public function getmodules(){
        $url = $this->api . '/modules';
        try {
            $response = Http::accept('application/json')->withToken($this->token)->get($url);
            
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();}
        }
    public function getmodule($id){
        $url = $this->api . '/modules/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->get($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function createmodule(array $data){
        $url = $this->api . '/modules';
        try {
            $response = Http::accept('application/json')->withToken($this->token)->post($url, $data);
             $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function updatemodule($id, array $data){
        $url = $this->api . '/modules/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->put($url, $data);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function deletemodule($id){
        $url = $this->api . '/modules/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->delete($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getsubmodules(){
        $url = $this->api . '/submodules';
        try {
            $response = Http::accept('application/json')->withToken($this->token)->get($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();}
        }

    public function getsubmodule($id){
        $url = $this->api . '/submodules/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->get($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function createsubmodule(array $data){
        $url = $this->api . '/submodules';
        try {
            $response = Http::accept('application/json')->withToken($this->token)->post($url, $data);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function updatesubmodule($id, array $data){
        $url = $this->api . '/submodules/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->put($url, $data);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function deletesubmodule($id){
        $url = $this->api . '/submodules/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->delete($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getpermissions()
    {
        $url = $this->api . '/permissions';
        try {
            $response = Http::accept('application/json')->withToken($this->token)->get($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function getpermission($id)
    {
        $url = $this->api . '/permissions/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->get($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function createpermission(array $data){
        $url = $this->api . '/permissions';
        try {
            $response = Http::accept('application/json')->withToken($this->token)->post($url, $data);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function updatepermission($id, array $data){
        $url = $this->api . '/permissions/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->put($url, $data);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function deletepermission($id){
        $url = $this->api . '/permissions/' . $id;
        try {
            $response = Http::accept('application/json')->withToken($this->token)->delete($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function updateProfile(array $data)
    {
        $url = $this->api . '/profile';
        try {
            $response = Http::accept('application/json')
                ->withToken($this->token)
                ->put($url, $data);
            return $response;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function updatePassword(array $data)
    {
        $url = $this->api . '/profile/password';
        try {
            $response = Http::accept('application/json')
                ->withToken($this->token)
                ->put($url, $data);
            return $response;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function getaccountsettings(){
        $url = $this->api . '/accountsettings';
        try {
            
            $response = Http::accept('application/json')->withToken($this->token)->get($url);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function createaccountsetting(array $data){
        $url = $this->api . '/accountsettings';
        try {
            $response = Http::accept('application/json')->withToken($this->token)->post($url, $data);
            $payload= $response->object();
            if($payload==null){
                return $response->body();
            }
            return $payload;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

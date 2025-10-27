<?php

namespace App\implementation\repositories;

use App\Enums\ApiResponse;
use App\Interfaces\repositories\iauthInterface;
use App\Models\Accounttype;
use App\Models\Resetpassword;
use App\Models\User;
use App\Models\Userapprovalcode;
use App\Notifications\PasswordResetNotification;
use App\Notifications\Welcomenotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class _authRepository implements iauthInterface
{
    /**
     * Create a new class instance.
     */
    protected $auth;
    protected $user;
    protected $userapprovalcode;
    protected $accounttype;
    protected $resetpassword;
    public function __construct(Auth $auth, User $user, Userapprovalcode $userapprovalcode, Accounttype $accounttype, Resetpassword $resetpassword)
    {
        $this->auth = $auth;
        $this->user = $user;
        $this->userapprovalcode = $userapprovalcode;
        $this->accounttype = $accounttype;
        $this->resetpassword = $resetpassword;
    }

    public function Login(array $credentials){
        if(Auth::attempt($credentials)){
           
                return true;
             
        }
        return false;
    }
     public function Logout(){
       Auth::logout();
     }
     public function register(array $credentials){
        try {
    
        $user = $this->user->create([
            'name' => $credentials['name'],
            'email' => $credentials['email'],
            'phonenumber' => $credentials['phonenumber'],
            'country' => $credentials['country'],
            'password' => $credentials['password'],
        ]);
        if($user){
            $user->accounttypes()->attach($credentials['accounttypes']);
            $accounttypes =  $this->accounttype->with('roles')->whereIn('id', $credentials['accounttypes'])->get();
            foreach($accounttypes as $accounttype){
                foreach($accounttype->roles as $role){
                    $user->assignRole($role->name);
                }
            }
            $user->notify(new Welcomenotification($user->name));
            return ['status' => ApiResponse::SUCCESS->value, 'message' => 'Account has been registered successfully.'];
        }
               
    } catch (\Exception $e) {
        return ['status' => false, 'message' => $e->getMessage()];
    }
    }
     public function forgotpassword(array $credentials){
        try {
            $user = User::where('email', $credentials['email'])->first();
            
            if (!$user) {
                return ['status' => "error", 'message' => 'We cannot find a user with that email address.'];
            }

            // Generate token
            $token = Str::random(64);

            // Store the token
            $user->resetpasswords()->create([
                'token' => $token,
                'expires_at' => now()->addDays(1)
            ]);

            // Send notification
            $user->notify(new PasswordResetNotification($token, $user->name));

            return ['status' => 'success', 'message' => 'We have emailed your password reset link.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
     }
     public function resetpassword(array $credentials){
        try {
            $token = $credentials['token'];
            $checktoken = $this->resetpassword->with('user')->where('token', $token)->first();
            if ($checktoken !=null) {
                if($checktoken->expires_at < now()){
                    return ['status' => 'error', 'message' => 'Token has expired.'];
                }elseif($checktoken->user->email != $credentials['email']){
                    return ['status' => 'error', 'message' => 'Invalid email.'];
                }else{
                    $checktoken->user->password = $credentials['password'];
                    $checktoken->user->save();
                    $checktoken->user->notify(new PasswordResetNotification($token, $checktoken->user->name));
                    return ['status' => 'success', 'message' => 'Password has been reset successfully.'];
                }
            }
            return ['status' => 'error', 'message' => 'Invalid token.'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
     }
     public function getprofile(){
        return Auth::user();
     }
     public function updateprofile(array $payload){
       
        try {
            $user = $this->user->where('id', Auth::user()->id)->first();
            $user->name = $payload['name'];
            $user->email = $payload['email'];
            $user->phonenumber = $payload['phonenumber'];
            $user->country = $payload['country'];
            $user->save();
            return ['status' => "success", 'message' => 'Profile updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
     }
     public function updatepassword(array $payload){
        try {
            $user = $this->user->where('id', Auth::user()->id)->first();
            if(!Hash::check($payload['current_password'], $user->password)) {
                return ['status' => "error", 'message' => 'Current password is incorrect'];
            }
            $user->password = $payload['password'];
            $user->save();
            return ['status' => "success", 'message' => 'Password updated successfully'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
     }

     public function updateapprovalcode(array $data){
        try {
            $user = $this->userapprovalcode->where('user_id', Auth::user()->id)->first();
           if($user){
            $user->code = Hash::make($data['approvalcode']);
            $user->expiry_date = now()->addDays(30);
            $user->save();
            return ['status' => "success", 'message' => 'Approval code updated successfully'];
           }else{
            $user = $this->userapprovalcode->create([
                'user_id' => Auth::user()->id,
                'code' => Hash::make($data['approvalcode']),
                'expiry_date' => now()->addDays(30)
            ]);
            return ['status' => "success", 'message' => 'Approval code updated successfully'];
           }
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
     }
     public function checkapprovalcode($approvalcode){
        try {
            $user = $this->userapprovalcode->where('user_id', Auth::user()->id)->first();
            if($user){
                if($user->expiry_date < now()){
                    return ['status' => "error", 'message' => 'Approval code has expired'];
                }
                if(Hash::check($approvalcode, $user->code)){
                    return ['status' => "success", 'message' => 'Approval code is correct'];
                }else{
                    return ['status' => "error", 'message' => 'Approval code is incorrect'];
                }
            }
            return ['status' => "error", 'message' => 'Approval code is not set'];
        } catch (\Exception $e) {
            return ['status' => "error", 'message' => $e->getMessage()];
        }
     }
}

<?php

namespace App\Listeners;

use App\Interfaces\repositories\iauthInterface;
use App\Interfaces\repositories\iuserInterface;
use Dcblogdev\MsGraph\MsGraph;
use Illuminate\Support\Facades\Auth;

class NewMicrosoft365SignInListener
{
    protected $userrepo;
    protected $authrepo;
    public function __construct(iuserInterface $userrepo, iauthInterface $authrepo)
    {
        $this->userrepo = $userrepo;
        $this->authrepo = $authrepo;
    }
    public function handle(object $event): void
    {


        $user = $this->userrepo->getuserbyemail($event->token['info']['mail'] ?? $event->token['info']['userPrincipalName']);
    
        if ($user == null) {


           $response =  $this->userrepo->createuser([
                'name' => $event->token['info']['displayName'],
                'email' => $event->token['info']['mail'] ?? $event->token['info']['userPrincipalName'], 
            ], [1]);
            if($response['status']=="success"){
                $user = $this->userrepo->getuserbyemail($event->token['info']['mail'] ?? $event->token['info']['userPrincipalName']);
            }else{
                abort(500, $response['message']);
            }
            
        }
       
        (new MsGraph)->storeToken(
            $event->token['accessToken'],
            $event->token['refreshToken'],
            $event->token['expires'],
            $user->id,
            $user->email
        );

        Auth::login($user);
    }
}

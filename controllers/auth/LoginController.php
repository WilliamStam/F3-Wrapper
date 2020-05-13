<?php
namespace controllers\auth;


use \system\Output;
use \system\Profiler;
use \system\Debug;
use \system\utilities\Arrays;
use \system\utilities\Strings;
use \system\utilities\System;
use \models\auth\LoginModel;

class LoginController extends AbstractController {
    
    function page() {

        $data = array();
        $data['email'] = $this->system->get("COOKIE.email");
        $data['errors'] = array();
        $data['messages'] = $this->messages();

        

        
        
        if ($this->system->get("VERB")=="POST"){
            $data['errors']['email'][] = "Login details not recognized";
            $data['errors']['password'][] = "";

            $data['email'] = $this->system->get("POST.email");
            $data['from'] = $this->system->get("POST.from");

            $password = $this->system->get("POST.password");
            
            $this->system->set("COOKIE.email", $data['email'], $this->system->get("CONFIG")['COOKIE_AGE']);

            $loginObject = (new LoginModel($this->system->get("SID")));
            $login = $loginObject->login($data['email'],$password);

            $data['attempts'] = $loginObject->getAttempts();

            if ($loginObject->getAttempts() > $this->system->get("CONFIG")['AUTH']['LOGIN']['ATTEMPTS'] / 3){
                $attempts_remaining = $this->system->get("CONFIG")['AUTH']['LOGIN']['ATTEMPTS'] - $data['attempts'];

                $data['messages'][] = array(
                    "type"=>"danger",
                    "message"=>"{$attempts_remaining} Login attempts remaining for this session"
                );
            }

            if ($login){
                if ($this->system->get("POST.from")){
                    $this->system->reroute($this->system->get("POST.from"));
                }
                $this->system->reroute("@index");
            }
        }
        
        
        $this->render("auth/login.twig", $data);
    }
}
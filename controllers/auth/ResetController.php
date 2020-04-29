<?php
namespace controllers\auth;

use models\auth\ResetModel;
use \system\Output;
use \system\Profiler;
use \system\Debug;
use \system\utilities\Arrays;
use \system\utilities\Strings;
use \system\utilities\System;

use \models\users\UserModel;

class ResetController extends AbstractController {

    function page() {

        $data = array();
        $data['code'] = $this->system->get("GET.code");
        $data['email'] = $this->system->get("COOKIE.email");
        $data['errors'] = array();
        $data['messages'] = $this->messages();
       

        $data['sent'] = "0";

        if ($this->system->get("VERB") == "POST") {
            $data['email'] = $this->system->get("POST.email");
            $this->system->set("COOKIE.email", $data['email']);
            
            $data['code'] = $this->system->get("POST.code");
            
            
            $password_1 = $this->system->get("POST.password");
            $password_2 = $this->system->get("POST.retype_password");
            if ($password_1 != $password_2){
                $data['errors']['retype_password'][] = "The password didnt match";
                $data['errors']['password'][] = "The retype password didnt match";
            }

           
            // TODO: check password strength, and if its been pawned



            if (!count($data['errors'])){
                // INFO: we assume all is ok with the stuff. now for the main check
                
                $reset = new ResetModel($this->system->get("SID"));
                $user = $reset->checkCode($data['email'],$data['code']);

                $data['attempts'] = $reset->getAttempts();

                if ($reset->getAttempts() > $this->system->get("CONFIG")['RESET']['ATTEMPTS'] / 3){
                    $attempts_remaining = $this->system->get("CONFIG")['RESET']['ATTEMPTS'] - $data['attempts'];

                    $data['messages'][] = array(
                        "type"=>"danger",
                        "message"=>"{$attempts_remaining} Reset attempts remaining for this session"
                    );
                }

                // System::debug($user);
                if ($user){
                    $user->setPassword($password_1);
                    $user->save();



                    $this->system->reroute("@auth_login?success=Password+reset+successfuly");
                } else {
                    $data['errors']['email'][] = "";
                    $data['errors']['code'][] = "";
                    $data['messages'][] = array(
                        "type"=>"danger",
                        "message"=>"Either the email or the code don't match or the code has expired or been used already"
                    );
                }
            }

            


            

        }

        

        $this->render("Auth/reset.twig", $data);
    }

}
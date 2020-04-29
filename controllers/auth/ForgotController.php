<?php
namespace controllers\auth;

use models\auth\ForgotModel;
use \system\Output;
use \system\Profiler;
use \system\Debug;
use \system\Template;
use \system\utilities\Arrays;
use \system\utilities\Strings;
use \system\utilities\System;

use \models\auth\ResetModel;
use \models\users\UserModel;

class ForgotController extends AbstractController {
    
    function page() {

        $data = array();
        $data['email'] = $this->system->get("COOKIE.email");
        $data['errors'] = array();
        $data['messages'] = $this->messages();

        


        $data['messages'][] = array(
            "type"=>"info",
            "message"=>"We will send a password reset link to this email address if this belongs to a valid user"
        );

        if ($this->system->get("CONFIG")['SMTP']['ENABLED'] == false){
            $data['messages'][] = array(
                "type"=>"danger",
                "message"=>"Our mail sending capabilities are dead!"
            );      
        }

        if ($this->system->get("VERB")=="POST"){

            $data['email'] = $this->system->get("POST.email");
            $this->system->set("COOKIE.email", $data['email']);

            $user = (new UserModel())
                ->_where("email = :EMAIL",array(":EMAIL"=>$data['email']))
                ->get();

            if ($user->getId()){

                $resetObject = new ForgotModel($this->system->get("SID"));
                $code = $resetObject->generateCode($user);

                $data['attempts'] = $resetObject->getAttempts();

                if ($resetObject->getAttempts() > $this->system->get("CONFIG")['FORGOT']['ATTEMPTS'] / 3){
                    $attempts_remaining = $this->system->get("CONFIG")['FORGOT']['ATTEMPTS'] - $data['attempts'];

                    $data['messages'][] = array(
                        "type"=>"danger",
                        "message"=>"{$attempts_remaining} code generate remaining for this session"
                    );
                }

                $template = new Template("auth/emails/reset_link.twig");
                $template->user = $user;
                $template->code = $code;
                $template->url = $this->system->get("SCHEME") . "://" . $this->system->get("HOST") . $this->system->alias("auth_reset") . "?code=" . $code;

                $body = $template->render();

                
                try {
                    $email = System::mail();
                    $email->set("To",$user->getEmail());
                    $email->set("Subject",$this->system->get("PACKAGE")." | Password reset link");

                    //$email->send($body);
                } catch (\Throwable $e){
                    $this->system->reroute('@auth_forgot?error=Email+couldnt+be+sent+at+this+time');
                }
            }
            $this->system->reroute('@auth_reset?success=Email+was+sent+with+the+code');

        }
        $this->render("Auth/forgot.twig", $data);

    }

   
}
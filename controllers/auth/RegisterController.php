<?php
namespace controllers\auth;

use models\auth\ForgotModel;
use models\auth\RegisterModel;

use \system\Output;
use \system\Profiler;
use \system\Debug;
use \system\Template;
use \system\utilities\Arrays;
use \system\utilities\Strings;
use \system\utilities\System;

use \services\Mail;

use \models\users\UserModel;

class RegisterController extends AbstractController {

    function page() {

        $data = array();
        $data['email'] = $this->system->get("COOKIE.email");
        $data['errors'] = array();
        $data['messages'] = $this->messages();
       
        $values = array();

        $data['sent'] = "0";

        if ($this->system->get("VERB") == "POST") {
            $data['email'] = $this->system->get("POST.email");
            $data['name'] = $this->system->get("POST.name");

            $this->system->set("COOKIE.email", $data['email']);
            
            $values['email'] = $data['email'];
            $values['name'] = $data['name'];
            
            
           if ($values['name']==""){
                $data['errors']["name"][] = "Name is required";
           }

              


                
            if (!count($data['errors'])){
                // INFO: we assume all is ok with the stuff. now for the main check
                
                $register = (new RegisterModel($this->system->get("SID")))
                    ->register($data['email'],$values);


                $data['errors'] = Arrays::merge($data['errors'],$register->getErrors());
            }

            if (!count($data['errors'])){

                $user = (new UserModel())
                    ->_where("email = :EMAIL",array(":EMAIL"=>$data['email']))
                    ->get();


                $resetObject = new ForgotModel($this->system->get("SID"));
                $code = $resetObject->generateCode($user);
               
               
                $template = new Template("auth/emails/new_user.twig");
                $template->user = $user;
                $template->code = $code;
                $template->url = $this->system->get("SCHEME") . "://" . $this->system->get("HOST") . $this->system->alias("auth_reset") . "?code=" . $code;

                $body = $template->render();

                // echo $body;
                // exit();
                $sent = true;

                // System::debug($this->system->get("CONFIG"));

                $email = new Mail();
                $email->setTo($user->getEmail());
                $email->setSubject($this->system->get("PACKAGE")." | New user created");
                $email->setBody($body);

                if ($this->system->get("CONFIG")['SMTP']){
                    $sent = $email->send();
                }

                if (!$sent){
                    $this->system->reroute('@auth_register?error=Email+couldnt+be+sent+at+this+time');
                }


                $this->system->reroute("@auth_reset?success=Account+created,+please+reset+your+password");
            }


            

        }

        

        $this->render("auth/register.twig", $data);
    }

}
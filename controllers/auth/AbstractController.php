<?php

namespace controllers\auth;

use system\Output;
use system\Template;

abstract class AbstractController extends _ {
    

   function messages(){
        $return = array();
        if ($this->system->get("GET.success")){
            $return[] = array(
                "type"=>"success",
                "message"=>$this->system->get("GET.success")
            );
        }
        if ($this->system->get("GET.info")) {
            $return[] = array(
                "type" => "info",
                "message" => $this->system->get("GET.info"),
            );
        }
        if ($this->system->get("GET.warning")){
            $return[] = array(
                "type"=>"warning",
                "message"=>$this->system->get("GET.warning")
            );
        }
        if ($this->system->get("GET.error")){
            $return[] = array(
                "type"=>"danger",
                "message"=>$this->system->get("GET.error")
            );
        }
        return $return;
   }

}

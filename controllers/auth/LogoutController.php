<?php
namespace Controllers\Auth;

use \System\Output;
use \System\Profiler;
use \System\Debug;
use \System\Utilities\Arrays;
use \System\Utilities\Strings;
use \System\Utilities\System;

class LogoutController extends AbstractController {
    
    function page() {
        $this->system->clear("SESSION");
        $this->system->reroute("@index");
    }


}
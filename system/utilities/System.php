<?php
declare (strict_types = 1);
namespace System\utilities;

class System {
    

    static function debug(){
        $args = func_get_args();
		switch ( func_num_args() ) {
			case 0:
				exit();
				break;
			case 1:
				$args = $args[0];
				break;
		}
       return new \System\Debug($args);
    }

    static function profiler($label = null, $component = null){
        $system = \Base::instance();
        return $system->get("PROFILER")->add(new \System\Profiler($label,$component));
       
    }
    static function collection($obj){
        $system = \Base::instance();
        return $system->get("COLLECTION")->add($obj);
       
    }
    

  
    

}
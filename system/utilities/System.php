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
    static function mail(){
        $system = \Base::instance();

        $return = new \SMTP(
            $system->get("CONFIG")['SMTP']['HOST'],
            $system->get("CONFIG")['SMTP']['PORT'],
            $system->get("CONFIG")['SMTP']['SCHEME'],
            $system->get("CONFIG")['SMTP']['USERNAME'],
            $system->get("CONFIG")['SMTP']['PASSWORD'],
            $system->get("CONFIG")['SMTP']['CTX'],
        );

        foreach ($system->get("CONFIG")['EMAIL_HEADERS'] as $key=>$value){
            $return->set($key, $value);
        }

        return $return;

    }

  
    

}
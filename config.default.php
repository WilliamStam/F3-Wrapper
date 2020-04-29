<?php

$storage = __DIR__ . DIRECTORY_SEPARATOR . "storage";
$return = array(
    // setting up some directories to use
	"DEBUG" => false,
    "TEMP" =>  $storage . DIRECTORY_SEPARATOR . "temp" ,
    "MEDIA" =>  $storage . DIRECTORY_SEPARATOR . "media" ,
    
    // by default the system writes its error file to this folder/YEAR_MONTH_VERSION.log
	"LOGS" => $storage . DIRECTORY_SEPARATOR . "logs" ,
	"CACHE" => $storage . DIRECTORY_SEPARATOR . "cache" ,
	"CACHE" => false,
	"ROOT" => __DIR__,

	"DB"=>array(
		'HOST' => 'localhost',
		'DATABASE' => 'database',
		'USERNAME' => '',
		'PASSWORD' => '',
		'PORT' => 3306,
		'CHARSET' => 'utf8',
		'COLLATION' => 'utf8_general_ci',
	),
    // CSRF token name. i like making it something like "first_name" and seeing the bots die!!!
    "CSRF"=>"CSRF",
    // system timezone
    "TZ"=>'Africa/Johannesburg',
    // fatfree includes a tag stripping function that uses this list to remove tags from user input
    "TAGS"=>'p,br,b,strong,i,italics,em,h1,h2,h3,h4,h5,h6,div,span,blockquote,pre,cite,ol,li,ul', 
    
    // login attempts to stop brute force
	"LOGIN"=>array(
		"ATTEMPTS"=>5,
        "MINUTES"=>10,
    ),
    // auth reset stuff
    "RESET"=>array(
		"ATTEMPTS"=>5,
        "MINUTES"=>10,
        "TOKEN_MINUTES"=>10 // how long a token is valid for
    ),
    // auth reset stuff
    "FORGOT"=>array(
		"ATTEMPTS"=>5,
        "MINUTES"=>10,
        
    ),
    "SMTP"=>array(
        "HOST"=>"localhost",
        "PORT"=>25,
        "SCHEME"=>NULL,
        "USERNAME"=>NULL,
        "PASSWORD"=>NULL,
        "CTX"=>NULL,
        "ENABLED"=>false
    ),
    // "SMTP"=>False,
    "EMAIL_HEADERS"=>array(
        "Errors-to"=>"info@growbox.co.za",
        "Reply-to"=>"info@growbox.co.za",
        "From"=>"system@growbox.co.za",
        "sender"=>"system@growbox.co.za",
    ),

    
    // Seed
    "SEED"=>'$uem&Sl&#hds*U@1!VP15HLvZTd1i$C8U7t7Ds92m$DUd2'
);

return $return;
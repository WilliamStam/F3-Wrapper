# F3 Framework Wrapper

## Setup

### Prepare system
- get composer working [https://getcomposer.org/]
- cd to root of project and type `composer install`
- Point webserver to the /public folder (contains index.php)
- install nodejs [https://nodejs.org/en/]
- `npm install -g node-sass` install node-sass globaly
- `npm install -g gulp` install gulp globaly 
- cd to the root of the project
- `npm install`

test that gulp works with `gulp --version`

- `gulp watch` will monitor the assets items and auto generate them to the Assets/[css,js]/.. folder from the Assets/[_css/_js]/.. sources

## Config

In the root of the project is a `config.default.php` file. this file contains the default config options. DO NOT EDIT THIS FILE. 
Create a `config.php` file in the root that returns an array of the config options that need to be changed, similar to `config.default.php`

by default the system creates a /storage folder. if you change the location of your storage / logs / cache folders please make sure the webserver has write privilages accordingly. 

i suggest you enable DEBUG mode in your config.php file while developing. 
```
$return = array(
	"DEBUG"=>true,
    ...
);
return $return;
```    
## Blank DB

a `database.sql` file is supplied in the root. import this into mysql. contains some of the default tables needed.

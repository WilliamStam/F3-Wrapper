<?php
declare (strict_types = 1);
namespace system;
use \system\utilities\System;
use \system\utilities\Strings;

class Assets {

    protected $FOLDER;
    protected $PATH;

    function __construct() {
        $this->system = \Base::instance();
    }
    function setFolder($folder) {
        $this->FOLDER = $folder;
    }
    function setPath($path) {
        $this->PATH = $path;
    }

    function render() {
        $fullpath = Strings::fixDirSlashes($this->FOLDER . DIRECTORY_SEPARATOR . $this->PATH);

        // System::debug($fullpath);

        if (!file_exists($fullpath)) {
            $this->system->error(404, "Asset can't be found [" . $this->PATH . "]");
        }

        $mimetype = Strings::getMimeType($fullpath);
        $etag = md5($this->system->get('VERSION') . "|" . $this->PATH);

        $this->setCacheing($fullpath, $mimetype, $etag);

        if (substr($mimetype, 0, 6) == "image/") {
            $get_interested = array("width", "height");
            $get_params = array_keys($_GET);

            /* INFO: we only need to do stuff when theres a query string added to resize the image etc */
            $img_work = count((array) array_intersect($get_interested, $get_params)) ? true : false;

            if ($img_work) {
                /* INFO: start resizing etc */
                $width = isset($_GET['width']) ? $_GET['width'] : null;
                $height = isset($_GET['height']) ? $_GET['height'] : null;
                $crop = isset($_GET['crop'])?$_GET['crop']:false;


                $etag = $etag . "|{$width}|{$height}|{$crop}";
                $this->setEtag($etag);


                $image = new \Image($this->PATH, null, $this->FOLDER . DIRECTORY_SEPARATOR);

                $resize_to = array(
                    "w"=>$width,
                    "h"=>$height
                );
    
                $crop_to = array();
                $want_size = array(
                    "w"=>$width,
                    "h"=>$height
                );
                if ($crop!==false){
                    $origH = $image->height();
                    $origW = $image->width();
    
                    $wantW = $width;
                    $wantH = $height;
    
                    
    
                    $resize_ratio_w = $origW / $width;
                    $resize_ratio_h = $origH / $height;
    
                    if ($resize_ratio_w < $resize_ratio_h){
                        $height = $origH /  $resize_ratio_w;
                    } else {
                         $width = $origW /  $resize_ratio_h;
                    }
    
                    
                }


                
                $image->resize($width, $height, $crop);

                if ($crop!==false && $width && $height){
                

                    $crop_parts = $this->system->split($crop);
                    if ($crop_parts[1] == null){
                        $crop_parts[1] = $crop_parts[0];
                    }
    
                    $crop_to = array(
                        "x1"=>0,
                        "x2"=>$width,
                        "y1"=>0,
                        "y2"=>$height,
                    );
    
    
                    if (! in_array($crop_parts[0],array("0","1","2"))){
                        $crop_parts[0] = "1";
                    }
                    if (! in_array($crop_parts[1],array("0","1","2"))){
                        $crop_parts[1] = "1";
                    }
                    
    
                    switch ($crop_parts[0]){ // X
                        case "0": // left
                            $crop_to['x1'] = 0;
                            $crop_to['x2'] = $wantW;
                            break;
                        case "1": // center
                            $offset = ($width - $wantW);
                            $offset = $offset / 2;
    
                            $crop_to['x1'] = $offset > 0 ? $offset : 0;
                            $crop_to['x2'] = $crop_to['x1'] + $wantW - 1;
                            break;
                        case "2": // right
                            $offset = ($width - $wantW);
                            $crop_to['x1'] = $offset > 0  ? $offset : 0 ;
                            $crop_to['x2'] = $width - 1;
                            break;
                    }
    
                    switch ($crop_parts[1]){ // Y
                        case "0": // top
                            $crop_to['y1'] = 0;
                            $crop_to['y2'] = $wantH;
                            break;
                        case "1": // center
                            $offset = ($height - $wantH);
                            $offset = $offset / 2;
                            $crop_to['y1'] = $offset > 0  ? $offset : 0 ;
                            $crop_to['y2'] = $crop_to['y1'] + $wantH - 1;
                            break;
                        case "2": // bottom
                            $offset = ($height - $wantH);
                            $crop_to['y1'] = $offset > 0  ? $offset : 0 ;
                            $crop_to['y2'] = $height - 1;
                            break;
                    }
    
                  
    
                    $image->crop( $crop_to['x1'],$crop_to['y1'],$crop_to['x2'],$crop_to['y2'] );
                }


                $outputMime = str_replace("image/", "", $mimetype);
                $image->render($outputMime);
                exit();

            }

        }
        $this->setEtag($etag);

        $contents = (new \System\Output())->replace(file_get_contents($fullpath));
        

        echo $contents;
        exit();
    }

    function setEtag($etag) {
        header("Etag: '" . $etag . "'");

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == "'" . $etag . "'" && !$this->system->get("DEBUG")) {
            header('HTTP/1.1 304 Not Modified');
            header_remove("Cache-Control");
            header_remove("Pragma");
            header_remove("Expires");
            exit();
        }
    }

    function setCacheing($fullpath, $mimetype, $etag) {
        $mimetype = Strings::getMimeType($fullpath);

        if ($this->system->get("DEBUG")) {
            $ts = gmdate("D, d M Y H:i:s") . " GMT";

            header("Expires: $ts");
            header("Last-Modified: $ts");
            header("Pragma: no-cache");
            header("Cache-Control: no-cache, must-revalidate");

        } else {

            $seconds_to_cache = ((60 * 60) * 24) * 7; // 7 day cache

            $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";

            header("Expires: $ts");
            header("Pragma: cache");
            header("Cache-Control: max-age={$seconds_to_cache}");

        }

        header("Content-Type: " . $mimetype);
        header("charset: UTF-8");
    }

}
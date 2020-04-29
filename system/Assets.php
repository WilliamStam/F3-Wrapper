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
                $crop = isset($_GET['crop']) && $_GET['crop'] ? true : false;

                $c = $crop ? "1" : "0";
                $etag = $etag . "|{$width}|{$height}|{$c}";
                $this->setEtag($etag);

                $image = new \Image($this->PATH, null, $this->FOLDER . DIRECTORY_SEPARATOR);
                $image->resize($width, $height, $crop);

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
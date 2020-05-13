<?php
declare (strict_types = 1);
namespace system;

use System\utilities\Arrays;
use \system\utilities\Strings;

class Output {

    const AUTO = "1";
    const JSON = "2";
    const HTML = "3";

    protected $BODY;
    protected $DATA = array();
    protected $FORMAT = self::AUTO;
    protected $PROFILER = array();

    function __construct() {

    }

    function setProfiler($profiler) {
        $system = \Base::instance();
        if (!$system->get("DEBUG")) {
            return $this;
        }

        $maxTime = 0;
        $maxMemory = 0;

        $timeStart = $profiler->first()->getTimeStart();
        $timeEnd = $profiler->first()->getTimeEnd();

        $counters = array();
        foreach ($profiler as $item) {
            if ($item->getTime() > $maxTime) {
                $maxTime = $item->getTime();
            }
            if ($item->getMemory() > $maxMemory) {
                $maxMemory = $item->getMemory();
            }
            if ($item->getTimeStart() < $timeStart) {
                $timeStart = $item->getTimeStart();
            }
            if ($item->getTimeEnd() > $timeEnd) {
                $timeEnd = $item->getTimeEnd();
            }

            // $key = Strings::toAscii($item->getComponent())."|".Strings::toAscii($item->getLabel());
            // if (!isset($counters[$key])){
            //     $counters[$key] = 0;
            // }
            // $counters[$key] = $counters[$key] + 1;

        }

        $data = array(
            "items" => array(),
        );
        foreach ($profiler as $item) {
            $key = Strings::toAscii($item->getComponent()) . "|" . Strings::toAscii($item->getLabel());
            if (!isset($counters[$key])) {
                $counters[$key] = 0;
            }
            $counters[$key] = $counters[$key] + 1;

            $output = array(
                "count" => $counters[$key],
                "label" => $item->getLabel(),
                "component" => $item->getComponent(),
                "colour" => Strings::stringToColorCode($item->getComponent()),
                "time" => array(
                    "total" => $item->getTime(),
                    "start" => $item->getTimeStart(),
                    "end" => $item->getTimeEnd(),
                ),
                "memory" => array(
                    "total" => $item->getMemory(),
                    "start" => $item->getMemoryStart(),
                    "end" => $item->getMemoryEnd(),
                ),
            );

            $percent = ($output['time']['total'] / $maxTime) * 100;

            $offsetStart = $output['time']['start'] - $timeStart;
            $offset = ($offsetStart / ($timeEnd - $timeStart)) * 100;

            // $offset =
            // $offset =

            $output['time']['percent'] = number_format($percent, 2, '.', '');
            $output['time']['offset'] = number_format($offset, 2, '.', '');

            $output['time']['display'] = number_format($output['time']['total'], 2);
            $output['memory']['display'] = sprintf('%.3f MB', ($output['memory']['total'] / 1024 / 1024));

            $data['items'][] = $output;
        }

        $data['SQL'] = $system->get("DB")->log();
        $data['id'] = Strings::toAscii(uniqid('', true));

        $this->PROFILER = $data;
        return $this;
    }

    function output() {
        $system = \Base::instance();

        if ($this->getFormat() == self::AUTO) {
            $this->setFormat(self::HTML);

            if ($system->ajax()) {
                $this->setFormat(self::JSON);
            }
            if (isset($_GET['json']) && $_GET['json']) {
                $this->setFormat(self::JSON);
            }
        }

        $body = "";
        switch ($this->getFormat()) {
        case SELF::HTML:
            $timerStr = "";
            if ($system->get("DEBUG")) {
                $profiler = json_encode($this->getProfiler());
                $timerStr = <<<Timer
                        <script>
                        try {
                        //  $('#profiler-modal').modal('show');
                            $('#PROFILER').jqotesub($('#template-profiler'), $profiler);
                        }
                        catch(err) {
                            console.log(err);
                        }
                        </script>
                        Timer;
            }
            $body = str_replace("<!--PROFILER-->", $timerStr, $this->getBody());
            break;
        case SELF::JSON:
            header("Content-Type: application/json");

            $data = $this->getData();
            if ($system->get("DEBUG")) {
                $data['PROFILER'] = $this->getProfiler();
                
            }

            Arrays::removeKeyFromArray($data, "_");

            $body = json_encode($data, JSON_PRETTY_PRINT);

            break;
        }

        $body = $this->replace($body);

        echo $body;

    }
    function replace($text) {
        $system = \Base::instance();
        $replace = array();
        foreach ($system->get("VARIABLES") as $k => $v) {
            $replace["@@" . $k . "@@"] = $v;
        };
        return strtr($text, $replace);
    }

    /**
     * Get the value of BODY
     */
    public function getBody() {
        return $this->BODY;
    }

    /**
     * Set the value of BODY
     *
     * @return  self
     */
    public function setBody($BODY) {
        $this->BODY = $BODY;

        return $this;
    }

    /**
     * Get the value of DATA
     */
    public function getData() {
        return $this->DATA;
    }

    /**
     * Set the value of DATA
     *
     * @return  self
     */
    public function setData($DATA) {
        $this->DATA = $DATA;

        return $this;
    }

    /**
     * Get the value of FORMAT
     */
    public function getFormat() {
        return $this->FORMAT;
    }

    /**
     * Set the value of FORMAT
     *
     * @return  self
     */
    public function setFormat($FORMAT) {
        $this->FORMAT = $FORMAT;

        return $this;
    }

    /**
     * Get the value of PROFILER
     */
    public function getProfiler() {
        return $this->PROFILER;
    }
}
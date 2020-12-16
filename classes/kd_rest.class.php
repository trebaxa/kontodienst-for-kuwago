<?php

/**
 * kd_master
 * 
 * @package konto_dienst
 * @author Harald Petrich
 * @copyright 2019 
 */

class kd_rest extends kd_master {

    public $_allow = array();
    public $_content_type = "application/json";
    public $_request = array();
    private $_method = "";
    private $_code = 200;

    /**
     * rest::__construct()
     * 
     * @return
     */
    public function __construct() {
        parent::__construct();
        $this->inputs();
    }

    /**
     * rest::get_referer()
     * 
     * @return
     */
    public function get_referer() {
        return $_SERVER['HTTP_REFERER'];
    }


    /**
     * rest::json()
     * 
     * @return
     */
    public function json($data) {
        if (is_array($data)) {
            return json_encode($data);
        }
    }


    /**
     * rest::response()
     * 
     * @return
     */
    public function response($data, $status = 200, $type = "") {
        $this->_code = ($status) ? $status : 200;
        $this->_content_type = ($type != "") ? $type : $this->_content_type;
        $this->set_headers();
        echo $data;
        exit;
    }

    /**
     * rest::response_text()
     * 
     * @return
     */
    public function response_text($data) {
        $this->response($data, 200, "text/plain");
    }

    /**
     * rest::response_json()
     * 
     * @return
     */
    public function response_json($data_arr) {
        $this->response($this->json($data_arr), 200, "application/json");
    }

    /**
     * kd_rest::error()
     * 
     * @param mixed $msge
     * @return void
     */
    public function error($msge) {
        $this->response($this->json(array('msge' => $msge)), 200, "application/json");
    }

    /**
     * rest::get_status_message()
     * 
     * @return
     */
    private function get_status_message() {
        $status = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported');
        return ($status[$this->_code]) ? $status[$this->_code] : $status[500];
    }

    /**
     * kd_rest::format_result()
     * 
     * @param mixed $data
     * @param mixed $msg
     * @param bool $fault
     * @return
     */
    public static function format_result($data, $msg = "", $fault = false) {
        return array(
            'status' => (($fault == true) ? 0 : 1),
            'msg' => $msg,
            'data' => self::arr_trim($data));
    }

    /**
     * rest::get_request_method()
     * 
     * @return
     */
    public function get_request_method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * rest::inputs()
     * 
     * @return
     */
    private function inputs() {
        switch ($this->get_request_method()) {
            case "POST":
                $this->_request = $this->clean_inputs($_POST);
                break;
            case "GET":
            case "DELETE":
                $this->_request = $this->clean_inputs($_GET);
                break;
            case "PUT":
                parse_str(file_get_contents("php://input"), $this->_request);
                $this->_request = $this->clean_inputs($this->_request);
                break;
            default:
                $this->response('', 406);
                break;
        }
    }

    /**
     * rest::clean_inputs()
     * 
     * @return
     */
    private function clean_inputs($data) {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->clean_inputs($v);
            }
        }
        else {
            if (get_magic_quotes_gpc()) {
                $data = trim(stripslashes($data));
            }
            $data = strip_tags($data);
            $clean_input = trim($data);
        }
        return $clean_input;
    }

    /**
     * rest::set_headers()
     * 
     * @return
     */
    private function set_headers() {
        header("HTTP/1.1 " . $this->_code . " " . $this->get_status_message());
        header("Content-Type:" . $this->_content_type);
    }
}

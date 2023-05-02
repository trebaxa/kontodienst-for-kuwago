<?php

/**
 * kd_master
 * 
 * @package konto_dienst
 * @author Harald Petrich
 * @copyright 2019 
 */

class kd_install extends kd_master {

    static $msge = array();
    static $msg = array();
    static $tpl = "";
    static $tpl_arr = array();


    /**
     * install_class::__construct()
     * 
     * @return void
     */
    function __construct() {
        if (!isset($_SESSION['set']))
            $_SESSION['set'] = array(
                'blz' => '',
                'user' => '',
                'pin' => '',
                'server' => '',
                'konto' => '');

    }

    /**
     * install_class::interpreter()
     * 
     * @return void
     */
    public function interpreter() {
        $cmd = "";
        if (isset($_REQUEST['cmd'])) {
            $cmd = 'cmd_' . $_REQUEST['cmd'];
            if (method_exists($this, $cmd)) {
                $this->$cmd();
            }
        }
    }

    /**
     * install_class::get_msg()
     * 
     * @return
     */
    public static function get_msg() {
        return static::$msg;
    }

    /**
     * install_class::get_msge()
     * 
     * @return
     */
    public static function get_msge() {
        return static::$msge;
    }
    /**
     * install_class::set_tpl()
     * 
     * @param mixed $tpl
     * @return void
     */
    public static function set_tpl($tpl) {
        static::$tpl = $tpl;
    }

    /**
     * install_class::set_tpl_arr()
     * 
     * @param mixed $var
     * @param mixed $val
     * @return void
     */
    public static function set_tpl_arr($var, $val) {
        static::$tpl_arr[$var] = $val;
    }

    /**
     * install_class::get_tpl_arr()
     * 
     * @param mixed $var
     * @return
     */
    public static function get_tpl_arr($var) {
        return static::$tpl_arr[$var];
    }
    /**
     * install_class::get_tpl()
     * 
     * @param mixed $tpl
     * @return
     */
    public static function get_tpl() {
        return static::$tpl;
    }
    /**
     * install_class::has_errors()
     * 
     * @return void
     */
    public static function has_errors() {
        count(static::$msge) > 0;
    }

    /**
     * install_class::set_msge()
     * 
     * @param mixed $str
     * @return void
     */
    public static function msge($str) {
        static::$msge[] = $str;
    }

    /**
     * install_class::msg()
     * 
     * @param mixed $str
     * @return void
     */
    public static function msg($str) {
        static::$msg[] = $str;
    }

    /**
     * install_class::gen_sid()
     * 
     * @param integer $length
     * @return
     */
    protected static function gen_sid($length = 8) {
        $key = "";
        $pattern = "1234567890abcdefghijklmnopqrstuvwxyz!#.ABCDEFGHIJKLMNOPQRSTUVWXYZ|";
            srand((double)microtime() * 1000000);
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern[rand(0, 35)];
        }
        return $key;
    }

    /**
     * install_class::gen_hash()
     * 
     * @return
     */
    protected static function gen_hash() {
        return md5(time() . $_SERVER['HTTP_HOST'] . rand(0, 10000));
    }

    /**
     * install_class::encrypt_password()
     * 
     * @param mixed $password_clear_text
     * @return
     */
    protected static function encrypt_password($password_clear_text, $hash_secret) {
        return password_hash($password_clear_text . $hash_secret, PASSWORD_BCRYPT, array("cost" => 10));
    }

    /**
     * install_class::verfriy_password()
     * 
     * @param mixed $password_clear_text
     * @param mixed $password_hash
     * @return
     */
    protected static function verfriy_password($password_clear_text, $hash_secret, $password_hash) {
        return password_verify($password_clear_text . $hash_secret, $password_hash);
    }

    /**
     * install::simple_crypt()
     * 
     * @param mixed $string
     * @param string $action
     * @param string $add
     * @return
     */
    public static function simple_crypt($string, $action = 'e', $add = '') {
        $secret_key = 'kjsdfh45JHFUh2euiJHfhdgsd' . $add;
        $secret_iv = 'dfgne9thjdfkgn0e9htoidfngfd0gher9gbdf' . $add;
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        if ($action == 'e') {
            $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        }
        else
            if ($action == 'd') {
                if ($string != "")
                    $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }

        return $output;
    }

    /**
     * install_class::get_domain_name()
     * 
     * @return
     */
    public static function get_domain_name() {
        $parts = explode('.', $_SERVER["HTTP_HOST"]);
        return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
    }

    /**
     * install_class::echoarr()
     * 
     * @param mixed $arr
     * @return void
     */
    public static function echoarr($arr) {
        echo '<pre>' . print_r($arr, true) . '</pre>';
    }

    /**
     * kd_install::curl_get_data()
     * 
     * @param mixed $url
     * @param mixed $vars
     * @return
     */
    public static function curl_get_data($url, $vars = array()) {
        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');

        if (is_array($vars) && count($vars) > 0) {
            curl_setopt($ch, CURLOPT_POST, 1);
            #  self::http_build_query_for_curl($vars, $curl_vars);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($vars));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 0) {
            $data = curl_error($ch);
            #            echo $url.' '.$data;
        }
        curl_close($ch);
        return $data;
    }

    /**
     * install_class::cmd_install()
     * 
     * @return void
     */
    function cmd_install() {
        $FORM = (array )$_POST['FORM'];
        $hash_secret = self::gen_hash();
        if (!empty($FORM['blz']) && !empty($FORM['konto']) && !empty($FORM['server'])) {
            $_SESSION['set'] = $FORM;
            $arr = array('api' => array(
                    'hash_secret' => $hash_secret,
                    'host' => self::get_domain_name(),
                    'key' => hash('sha256', self::get_domain_name(), false),
                    'secret' => self::encrypt_password(self::get_domain_name(), $hash_secret) . self::gen_sid(50)));
            $arr = array_merge($FORM, $arr);

            # save hash
            file_put_contents(CONFIG_PATH . 'fints.hash', $hash_secret);
            file_put_contents(CONFIG_PATH . 'fints_config.json', self::simple_crypt(json_encode($arr), 'e', $hash_secret));
            file_put_contents(CONFIG_PATH . '.htaccess', "order deny,allow\ndeny from all");

            self::set_tpl_arr('arr', $arr);
            self::set_tpl('install.tanmodes');
            self::msg('Konfiguration gespeichert');
        }
        else {
            self::msge('Fehler in den Angaben!');
        }

    }

    /**
     * kd_install::cmd_select_tanmode()
     * 
     * @return void
     */
    function cmd_select_tanmode() {
        $_SESSION['TANMODE'] = (array )$_POST['FORM'];
        self::set_tpl('install.tanmedium');
        self::msg('saved');
    }

    /**
     * kd_install::cmd_select_tanmedia()
     * 
     * @return void
     */
    function cmd_select_tanmedia() {
        if (!isset($_SESSION['TANMODE']) || !is_array($_SESSION['TANMODE'])) {
            header('location: install.php');
            exit();
        }
        $_SESSION['TANMODE'] = array_merge($_SESSION['TANMODE'], (array )$_POST['FORM']);       
        #   self::echoarr($_SESSION['TANMODE'] );die;
        $hash_secret = self::gen_hash();
        $arr = array('api' => array(
                'tanmode' => $_SESSION['TANMODE']['tanmode'],
                'tanmedia' => (isset($_SESSION['TANMODE']['tanmedia']) ? $_SESSION['TANMODE']['tanmedia'] : ""),
                'hash_secret' => $hash_secret,
                'host' => self::get_domain_name(),
                'key' => hash('sha256', self::get_domain_name(), false),
                'secret' => self::encrypt_password(self::get_domain_name(), $hash_secret) . self::gen_sid(50)));

        $arr = array_merge($_SESSION['set'], $arr);

        # save hash
        file_put_contents(CONFIG_PATH . 'fints.hash', $hash_secret);
        file_put_contents(CONFIG_PATH . 'fints_config.json', self::simple_crypt(json_encode($arr), 'e', $hash_secret));
        self::set_tpl_arr('arr', $arr);

        self::set_tpl('install.fine');
        self::msg('Konfiguration gespeichert');
    }

    /**
     * kd_install::print_msge()
     * 
     * @param mixed $str
     * @return void
     */
    public static function print_msge($str) {
        echo '<div class="alert alert-danger">' . $str . '</div>';
    }

}

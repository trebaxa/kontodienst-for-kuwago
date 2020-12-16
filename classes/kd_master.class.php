<?php

/**
 * kd_master
 * 
 * @package konto_dienst
 * @author Harald Petrich
 * @copyright 2019 
 */
define('CONFIG_PATH', './key/');

class kd_master {

    /**
     * kd_master::__construct()
     * 
     * @return void
     */
    public function __construct() {

    }

    /**
     * kd_master::load_config()
     * 
     * @return
     */
    public static function load_config() {
        if (file_exists(CONFIG_PATH . 'fints_config.json')) {
            $hash_secret = file_get_contents(CONFIG_PATH . 'fints.hash');
            $str = file_get_contents(CONFIG_PATH . 'fints_config.json');
            return json_decode(self::simple_crypt($str, 'd', $hash_secret), true);
        }
        else {
            return array();
        }
    }

    /**
     * kd_master::gen_trans_hash()
     * 
     * @param mixed $row
     * @return
     */
    public static function gen_trans_hash($row) {
        $arr = self::split_MT940($row['b_description']);
        $str = strtolower(implode('', $arr) . $row['b_valuta_date'] . (float)$row['b_amount'] . $row['b_text'] . $row['b_name']);
        return md5($str);
    }

    /**
     * kd_master::simple_crypt()
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
     * kd_master::echoarr()
     * 
     * @param mixed $arr
     * @return void
     */
    public static function echoarr($arr) {
        echo '<pre>' . print_r($arr, true) . '</pre>';
    }


    /**
     * kd_api::transform_konto_to_arr()
     * 
     * @param mixed $konto
     * @return
     */
    public static function transform_konto_to_arr($konto) {
        return array(
            'konto' => $konto->getAccountNumber(),
            'iban' => $konto->getIban(),
            'bic' => $konto->getBic(),
            'blz' => $konto->getBlz());
    }

    /**
     * kd_master::get_konto()
     * 
     * @param mixed $accounts
     * @return
     */
    public function get_konto($accounts) {
        foreach ($accounts as $key => $konto) {
            $konto_nr = $konto->getAccountNumber();
            if ($konto_nr == $this->fints_config['konto']) {
                return $konto;
            }
        }
        return "";
    }

    /**
     * kd_master::get_vzweck()
     * 
     * @param mixed $descr
     * @return
     */
    public static function get_vzweck($descr) {
        $arr = self::split_MT940($descr);
        $vzweck = $descr;
        if (strstr($descr, 'KREF') && !strstr($descr, 'SVWZ')) {
            $vzweck = trim(substr($descr, strpos($descr, 'KREF+') + 9 + 15));
        }
        if (isset($arr['SVWZ'])) {
            $vzweck = $arr['SVWZ'];
        }
        if (strstr($vzweck, 'DATUM')) {
            $vzweck = substr($vzweck, 0, strpos($vzweck, 'DATUM'));
        }
        if ($vzweck == "" && isset($arr['KREF'])) {
            $vzweck = $arr['KREF'];
        }
        return trim($vzweck);
    }

    /**
     * kd_master::get_regnum()
     * 
     * @return
     */
    public static function get_regnum() {
        return base64_decode('QkY3MzE0RjIyMUU5RDFFNDhDMTU0NTMzOA==');
    }

    /**
     * kd_master::split_MT940()
     * 
     * @param mixed $description
     * @return
     */
    public static function split_MT940($description) {
        // keywords to be isolated into separate results
        $keywords = array(
            'EREF', // End-to-End Reference
            'KREF', // Client / Orderer Reference
            'MREF', // Mandate Id
            'PREF', // Payment Reference
            'CRED', // Creditor ID
            'DEBT', // Debtor ID
            'ORDP', // Ordering Party
            'BENM', // Beneficiary
            'ULTC', // Ultimate Creditor
            'ULTD', // Ultimate Debtor
            'REMI', // Remittance Information
            'PURP', // Purpose Code
            'RTRN', // Return Reason
            'ACCW', // Counterparty Account and bank
            'IBK', // Intermediary Bank
            'OCMT', // Original Amount
            'COAM', // Compensation Amount
            'CHGS', // Charges
            'EXCH', // Exchange Rate
            'SVWZ', // Verwendungszweck
            'ABWA', // abweichender Auftraggeber
            );

        // split the concatenated description string into parts, including the keywords
        $parts = preg_split('/(' . implode('|', $keywords) . ')\+/', $description, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        // restructure result array (must be empty before!)
        unset($result);
        $result = array();

        // use item n as key and item n+1 as value
        for ($i = 0, $n = count($parts) - 1; $i < $n; $i += 2) {
            $result[$parts[$i]] = $parts[$i + 1];
        }
        return $result;
    }

    /**
     * kd_master::secure()
     * 
     * @return void
     */
    public static function secure() {
        if (file_exists('install.php')) {
            rename('install.php', 'install.ren');
        }
    }

    /**
     * kd_master::arr_trim()
     * 
     * @param mixed $arr
     * @return
     */
    public static function arr_trim($arr) {
        foreach ((array )$arr as $key => $wert)
            if (!is_array($wert)) {
                $arr[$key] = trim($arr[$key]);
            }
            else {
                $arr[$key] = self::arr_trim($arr[$key]);
            }
            return $arr;
    }
}

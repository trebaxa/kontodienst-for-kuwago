<?PHP

/**
 * kd_master
 * 
 * @package konto_dienst
 * @author Harald Petrich
 * @copyright 2019 
 */

#use Fhp\FinTs;
#use Fhp\Model\StatementOfAccount\Statement;
#use Fhp\Model\StatementOfAccount\Transaction;

use Fhp\CurlException;
use Fhp\Protocol\ServerException;
use Fhp\Protocol\UnexpectedResponseException;


class kd_api extends kd_rest {
    var $fints_config = array();

    /**
     * api::__construct()
     * 
     * @return void
     */
    public function __construct() {
        self::secure();
    }


    /**
     * api::process_api()
     * 
     * @return void
     */
    public function process_api() {
        $func = 'cmd_' . strtolower(trim(str_replace("/", "", $_REQUEST['cmd'])));
        # || 1==1
        if (($this->validate_request() === true && (int)method_exists($this, $func) > 0))
            $this->$func();
        else
            $this->response_json(array('msge' => 'api failed'), 404);
    }


    /**
     * api::init_fints()
     * 
     * @return void
     */
    protected function init_fints() {
        $this->fints_config = self::load_config();
        /*define('FHP_BANK_URL', $this->fints_config['server']); # HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
        define('FHP_BANK_CODE', $this->fints_config['blz']); # Your bank code / Bankleitzahl
        define('FHP_ONLINE_BANKING_USERNAME', $this->fints_config['user']); # Your online banking username / alias
        define('FHP_ONLINE_BANKING_PIN', $this->fints_config['pin']); # Your online banking PIN (NOT! the pin of your bank card!)
        define('FHP_REGISTRATION_NO', self::get_regnum());
        define('FHP_SOFTWARE_VERSION', '1.0'); # Your own Software product version
        $fints = new FinTs(FHP_BANK_URL, FHP_BANK_CODE, FHP_ONLINE_BANKING_USERNAME, FHP_ONLINE_BANKING_PIN, FHP_REGISTRATION_NO, FHP_SOFTWARE_VERSION);
        */
        $options = new \Fhp\Options\FinTsOptions();
        $options->url = $this->fints_config['server']; // HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
        $options->bankCode = $this->fints_config['blz']; // Your bank code / Bankleitzahl
        $options->productName = self::get_regnum(); // The number you receive after registration / FinTS-Registrierungsnummer
        $options->productVersion = '1.0'; // Your own Software product version
        $credentials = \Fhp\Options\Credentials::create($this->fints_config['user'], $this->fints_config['pin']); // This is NOT the PIN of your bank card!
        $fints = \Fhp\FinTs::new($options, $credentials);
        $tanMode = (int)$this->fints_config['api']['tanmode']; #  pushTAN is 921; // This is just a placeholder you need to fill!
        $tanMedium = ($this->fints_config['api']['tanmedia'] != "") ? $this->fints_config['api']['tanmedia'] : null; // This is just a placeholder you may need to fill.
        $fints->selectTanMode($tanMode, $tanMedium);
        $login = $fints->login();
        if ($login->needsTan()) {
            # handleTan($login);
            $this->error('tan has been requested. query not alloed. may too many days back.');
        }

        #$fints->setLogger(new \Tests\Fhp\CLILogger());
        return $fints;
    }

    /**
     * kd_api::validate_request()
     * 
     * @return
     */
    protected function validate_request() {
        $res = false;
        if (isset($_REQUEST['apikey']) && isset($_REQUEST['secret']) && isset($_REQUEST['cmd'])) {
            $this->fints_config = self::load_config();
            $res = ($this->fints_config['api']['key'] == $_REQUEST['apikey'] && $this->fints_config['api']['secret'] == $_REQUEST['secret']);
        }
        return $res;
    }

    /**
     * kd_api::cmd_test()
     * 
     * @return void
     */
    function cmd_test() {
        try {
            $this->response_json(self::format_result(array('msg' => 'api connection valid', 'api_status' => 'ok'), 'API OK'));
        }
        catch (Exception $e) {
            $this->response_json(self::format_result(array('msg' => 'api connection FAILED'), $e->getMessage(), true));
        }
    }

    /**
     * kd_api::load_accounts()
     * 
     * @return
     */
    function load_accounts($fints) {
        try {
            $getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
            $fints->execute($getSepaAccounts);
            if ($getSepaAccounts->needsTan()) {
                # handleTan($getSepaAccounts); // See login.php for the implementation.
                $this->error('tan has been requested. query not alloed. may too many days back.');
            }
            $accounts = $getSepaAccounts->getAccounts();
            return $accounts;
        }
        catch (Exception $e) {
            $this->response_json(self::format_result(array(), $e->getMessage(), true));
        }
    }

    /**
     * api::cmd_get_saldo()
     * 
     * @return void
     */
    function cmd_get_saldo() {
        try {
            $fints = $this->init_fints();
            $saldo = 0;
            $accounts = $this->load_accounts($fints);


            #$oneAccount = $getSepaAccounts->getAccounts()[0];
            $konto = $this->get_konto($accounts);

            $getBalance = \Fhp\Action\GetBalance::create($konto, false);
            $fints->execute($getBalance);
            if ($getBalance->needsTan()) {
                # handleTan($getSepaAccounts); // See login.php for the implementation.
                $this->error('tan has been requested. query not alloed. may too many days back.');
            }
            foreach ($getBalance->getBalances() as $hisal) {
                $accNo = $hisal->getAccountInfo()->getAccountNumber();
                $saldo = $hisal->getGebuchterSaldo()->getAmount();
                # $curr = $hisal->getGebuchterSaldo()->getCurrency();
                # $date = $hisal->getGebuchterSaldo()->getTimestamp()->format('Y-m-d');
            }


            #  $accounts = $fints->getSEPAAccounts();
            #  $konto = $this->get_konto($accounts);
            #  $saldo = $fints->getSaldo($konto);
            #  $fints->close();
            $saldo_arr = array('amount' => $saldo, 'konto' => self::transform_konto_to_arr($konto));
            $fints->close();
            $this->response_json(self::format_result($saldo_arr, 'get saldo successfull'));

        }
        catch (Exception $e) {
            $this->response_json(self::format_result(array(), $e->getMessage(), true));
        }
    }

    /**
     * kd_api::cmd_install()
     * 
     * @return void
     */
    function cmd_install() {
        if (file_exists('install.ren')) {
            rename('install.ren', 'install.php');
        }
        header('location: /install.php');
        exit();
    }

    /**
     * kd_api::cmd_get_accounts()
     * 
     * @return void
     */
    function cmd_get_accounts() {
        try {
            $fints = $this->init_fints();
            $accounts = $this->load_accounts($fints);
            foreach ($accounts as $key => $konto) {
                $konten[] = self::transform_konto_to_arr($konto);
            }
            $fints->close();
            $this->response_json(self::format_result($konten, 'get accounts successfull'));

        }
        catch (Exception $e) {
            $this->response_json(self::format_result(array(), $e->getMessage(), true));
        }
    }

    /**
     * api::cmd_get_statements()
     * 
     * @return void
     */
    function cmd_get_statements() {
        try {
            $fints = $this->init_fints();
            $accounts = $this->load_accounts($fints);
            $konto = $this->get_konto($accounts);
            $all = array();
            $date_back = date("Y-m-d", strtotime("-60 day"));
            $to = new \DateTime();
            $from = new \DateTime($date_back);
            #$soa = $fints->getStatementOfAccount($konto, $from, $to); #self::echoarr($soa);

            $getStatement = \Fhp\Action\GetStatementOfAccount::create($konto, $from, $to);
            $fints->execute($getStatement);
            if ($getStatement->needsTan()) {
                # handleTan($getStatement); // See login.php for the implementation.
                $this->error('tan has been requested. query not alloed. may too many days back.');
            }


            # if ($soa->isTANRequest()) {
            /*@unlink(__DIR__ . "/tan.txt");
            $serialized = serialize($soa);

            echo "Waiting max. 60 seconds for TAN<br>";

            for ($i = 0; $i < 60; $i++) {
            sleep(1);
            $tan = "";
            if (file_exists(__DIR__ . "/tan.txt")) {
            $tan = trim(file_get_contents(__DIR__ . "/tan.txt"));
            }
            if ($tan == "") {
            echo "No TAN found, waiting " . (60 - $i) . "!<br>";
            continue;
            }

            break;
            }

            $unserialized = unserialize($serialized);

            $fints = new FinTs(FHP_BANK_URL, FHP_BANK_CODE, FHP_ONLINE_BANKING_USERNAME, FHP_ONLINE_BANKING_PIN, FHP_REGISTRATION_NO, FHP_SOFTWARE_VERSION);
            #$fints->setLogger(new testLogger());

            $soa = $fints->finishStatementOfAccount($unserialized, $konto, $from, $to, $tan); */
            # $this->error('tan has been requested. query not alloed. may too many days back.');
            #  }

            #  $statements = $soa->getStatements();
            $soa = $getStatement->getStatement();
            foreach ($soa->getStatements() as $statement) {

                #  foreach ($statements as $statement) {
                foreach ($statement->getTransactions() as $transaction) {
                    $description = (string )$transaction->getDescription1();

                    $vzweck = self::get_vzweck($description);
                    $arr = self::split_MT940($description);
                    $trans = array(
                        # 'b_date' => $transaction->getBookingDate()->format('Y-m-d'),
                        # 'b_valuta_date' => $transaction->getValutaDate()->format('Y-m-d'),
                        # 'b_amount' => ($transaction->getCreditDebit() == Transaction::CD_DEBIT ? '-' : '') . $transaction->getAmount(),
                        # 'b_text' => $transaction->getBookingText(),
                        # 'b_description' => $description,
                        # 'b_description2' => (string )$transaction->getDescription2(),
                        # 'b_vzweck' => $vzweck,
                        # 'b_name' => $transaction->getName(),
                        # 'b_abwa' => ((isset($arr['ABWA'])) ? $arr['ABWA'] : ""),


                        'b_date' => $transaction->getBookingDate()->format('Y-m-d'),
                        'b_valuta_date' => $transaction->getValutaDate()->format('Y-m-d'),
                        'b_amount' => ($transaction->getCreditDebit() == \Fhp\Model\StatementOfAccount\Transaction::CD_DEBIT ? '-' : '') . $transaction->getAmount(),
                        'b_text' => $transaction->getBookingText(),
                        'b_description' => $description,
                        'b_description2' => (string )$transaction->getDescription2(),
                        'b_vzweck' => $vzweck,
                        'b_name' => $transaction->getName(),
                        'b_abwa' => ((isset($arr['ABWA'])) ? $arr['ABWA'] : ""),

                        );
                    if ($trans['b_name'] == "" && $trans['b_abwa'] != "") {
                        $trans['b_name'] = $trans['b_abwa'];
                    }
                    $trans['b_vormerk'] = (($trans['b_text'] == 'SONSTIGER EINZUG' || $trans['b_text'] == 'SAMMEL-LS-EINZUG') && $trans['b_name'] == '') ? 1 : 0;
                    $trans['id'] = self::gen_trans_hash($trans);
                    $all[] = $trans;
                }
            }
            $fints->close();
            $this->response_json(self::format_result($all, 'get statements successfull'));
        }
        catch (Exception $e) {
            $this->response_json(self::format_result(array(), $e->getMessage(), true));
        }
    }


}

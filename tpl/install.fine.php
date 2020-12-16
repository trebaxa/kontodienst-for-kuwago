<?PHP

?>
<div class="alert alert-warning">
    Benennen Sie nun UNBEDINGT die <b>install.php</b> um, damit kein Zugriff mehr darauf stattfinden kann.
    Ihre Konfiguration wurde ausserhalb des Roots der Domain auf Ihrem Server gespeichert.
</div>

<div class="alert alert-info">
<?php

echo 'API Key: ' . $INSTALL::get_tpl_arr('arr')['api']['key'] . '<br>';
echo 'API Secret: ' . $INSTALL::get_tpl_arr('arr')['api']['secret'] . '<br>';
echo 'API Server: https://' . $_SERVER['HTTP_HOST'] . str_replace(basename($_SERVER['PHP_SELF']),'',$_SERVER['PHP_SELF']) . '<br>';

?>
</div>

<h2>Testing...</h2>
<?php


file_put_contents(__DIR__ . "/state.log", "");
require 'lib/phpFinTS/vendor/autoload.php';

$fints_config = kd_install::load_config();

$fints = require_once 'login.php';

$getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
$fints->execute($getSepaAccounts);
if ($getSepaAccounts->needsTan()) {
    handleTan($getSepaAccounts); // See login.php for the implementation.
}
$accounts = $getSepaAccounts->getAccounts();
#$INSTALL::echoarr($accounts);
$oneAccount = $accounts[0];
foreach ($accounts as $account) {
    if ($account->getAccountNumber() == $fints_config['konto']) {
        $oneAccount = $account;
    }
}


#$INSTALL::echoarr($oneAccount);
$getBalance = \Fhp\Action\GetBalance::create($oneAccount, false);
$fints->execute($getBalance);
if ($getBalance->needsTan()) {
    handleTan($getBalance); // See login.php for the implementation.
}


/**
 @var \Fhp\Segment\SAL\HISAL $hisal */
#$INSTALL::echoarr($getBalance->getBalances());
foreach ($getBalance->getBalances() as $hisal) {
    $accNo = $konto = $hisal->getAccountInfo()->getAccountNumber();
    if ($hisal->getKontoproduktbezeichnung() !== null) {
        $accNo .= ' (' . $hisal->getKontoproduktbezeichnung() . ')';
    }
    $amnt = $hisal->getGebuchterSaldo()->getAmount();
    $curr = $hisal->getGebuchterSaldo()->getCurrency();
    $date = $hisal->getGebuchterSaldo()->getTimestamp()->format('Y-m-d');
    echo "<h3>On $accNo you have $amnt $curr as of $date.</h3>";
}

$date_back = date("Y-m-d", strtotime("-13 day"));
$to = new \DateTime();
$from = new \DateTime($date_back);

$getStatement = \Fhp\Action\GetStatementOfAccount::create($oneAccount, $from, $to);
$fints->execute($getStatement);
if ($getStatement->needsTan()) {
    # handleTan($getStatement); // See login.php for the implementation.
    $this->error('tan has been requested. query not alloed. may too many days back.');
}
$soa = $getStatement->getStatement();
foreach ($soa->getStatements() as $statement) {

    #  foreach ($statements as $statement) {
    foreach ($statement->getTransactions() as $transaction) {
        echo 'Amount      : ' . ($transaction->getCreditDebit() == \Fhp\Model\StatementOfAccount\Transaction::CD_DEBIT ? '-' : '') . $transaction->getAmount() . '<br>';
        echo 'Booking text: ' . $transaction->getBookingText() . '<br>';
        echo 'Name        : ' . $transaction->getName() . '<br>';
        echo 'Description : ' . $transaction->getMainDescription() . '<br>';
        echo 'EREF        : ' . $transaction->getEndToEndID() . '<br>';
        echo '=======================================' . '<br>' . '<br>';
    }
}

$fints->close();

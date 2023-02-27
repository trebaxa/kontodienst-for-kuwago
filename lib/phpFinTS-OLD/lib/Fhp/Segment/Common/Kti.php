<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung international (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.3.2
 */
class Kti extends BaseDeg implements AccountInfo
{
    /** @var string|null Max length: 34 */
    public $iban;
    /** @var string|null Max length: 11, required if IBAN is present. */
    public $bic;

    // The following fields can only be set if the BPD parameters allow it. If they are set, the fields above become
    // optional.
    /** @var string|null Also known as Depotnummer. */
    public $kontonummer;
    /** @var string|null */
    public $unterkontomerkmal;
    /** @var Kik|null */
    public $kreditinstitutskennung;

    /** {@inheritdoc} */
    public function validate()
    {
        parent::validate();
        if ($this->iban !== null) {
            if ($this->bic == null) {
                throw new \InvalidArgumentException('Kti cannot have IBAN without BIC');
            }
        } else {
            if ($this->kontonummer === null || $this->kreditinstitutskennung === null) {
                throw new \InvalidArgumentException('Kti must have IBAN+BIC or Kontonummer+Kik or both');
            }
        }
    }

    public static function create(string $iban, string $bic): Kti
    {
        $result = new Kti();
        $result->iban = $iban;
        $result->bic = $bic;
        return $result;
    }

    public static function fromAccount(SEPAAccount $account): Kti
    {
        $result = static::create($account->getIban(), $account->getBic());
        $result->kontonummer = $account->getAccountNumber();
        $result->unterkontomerkmal = $account->getSubAccount();
        $result->kreditinstitutskennung = Kik::create($account->getBlz());
        return $result;
    }

    /** {@inheritdoc} */
    public function getAccountNumber()
    {
        return $this->iban ?? $this->kontonummer;
    }

    /** {@inheritdoc} */
    public function getBankIdentifier()
    {
        return $this->bic ?? $this->kreditinstitutskennung->kreditinstitutscode;
    }
}
